<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Customer;
use Illuminate\Support\Facades\Session;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Schema;

class CustomerLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:customer')->except('logout', 'magicLogin');
    }

    public function showLoginForm(): View
    {
        return view('auth.customer-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string|min:1',
            'password' => 'required|string|min:8',
        ]);

        $identifier = trim($request->username);
        $password = trim($request->password);

        // Manual authentication to avoid password column issues
        $authProvider = app('auth')->guard('customer')->getProvider();
        $customer = $authProvider->retrieveByCredentials([
            'username' => $identifier,
            'password' => $password
        ]);

        if ($customer && $authProvider->validateCredentials($customer, [
            'username' => $identifier,
            'password' => $password
        ])) {
            // Manual login without password handling
            Auth::guard('customer')->login($customer, $request->filled('remember'));
            
            $request->session()->regenerate();
            if ($customer->tenant_id) {
                TenantContext::set((int) $customer->tenant_id);
            }
            
            // Update login tracking manually
            $this->updateLoginTracking($customer);
            $this->maybePromptPasswordChange($request, $customer);
            
            return redirect()->intended(route('customer.dashboard'))
                ->with('success', 'Welcome back, ' . $customer->name . '!');
        }

        throw ValidationException::withMessages([
            'username' => 'Invalid username or phone number.',
        ]);
    }

    private function updateLoginTracking($customer)
    {
        try {
            $updateData = [];
            
            if (\Schema::hasColumn('customers', 'last_login_at')) {
                $updateData['last_login_at'] = now();
            }
            
            if (\Schema::hasColumn('customers', 'login_count')) {
                $updateData['login_count'] = ($customer->login_count ?? 0) + 1;
            }
            
            if (!empty($updateData)) {
                // Use DB query to avoid model save issues
                \DB::table('customers')
                    ->where('id', $customer->id)
                    ->update($updateData);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to update customer login tracking: ' . $e->getMessage());
        }
    }

public function logout(Request $request)
{
    auth('customer')->logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    // Redirect to homepage after customer logout
    return redirect('/')->with('message', 'Successfully logged out');
}

public function magicLogin(Request $request, $customerId, $invoiceId)
{
    $customer = Customer::findOrFail($customerId);
    $invoice = $customer->invoices()->where('id', $invoiceId)->firstOrFail();

    Auth::guard('customer')->login($customer);
    $request->session()->regenerate();
    if ($customer->tenant_id) {
        TenantContext::set((int) $customer->tenant_id);
    }
    $this->updateLoginTracking($customer);
    $this->maybePromptPasswordChange($request, $customer);

    return redirect()->route('customer.invoices.show', $invoice->id);
}

private function maybePromptPasswordChange(Request $request, Customer $customer): void
{
    $loginCount = null;
    if (Schema::hasColumn('customers', 'login_count')) {
        $loginCount = (int) (($customer->login_count ?? 0) + 1);
    }

    if ($this->shouldPromptPasswordChange($customer, $loginCount)) {
        $request->session()->put('customer_password_prompt', true);
    }
}

private function shouldPromptPasswordChange(Customer $customer, ?int $loginCount): bool
{
    $isFirstLogin = $loginCount !== null ? $loginCount === 1 : (($customer->login_count ?? 0) <= 0);

    return $isFirstLogin
        && empty($customer->password)
        && empty($customer->password_change_skipped_at);
}
}
