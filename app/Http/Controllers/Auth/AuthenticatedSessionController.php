<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $tenants = Tenant::orderBy('name')->get();

        if ($tenants->isEmpty()) {
            $default = $this->ensureDefaultTenant();
            $tenants = collect([$default]);
        }

        return view('auth.login', compact('tenants'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $tenantId = $request->input('tenant_id');

        if ($request->boolean('create_company')) {
            if (!$user->hasRole('Super Admin')) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'company_name' => 'Only Super Admin can create a new company.',
                ]);
            }

            $companyName = trim((string) $request->input('company_name'));
            if ($companyName === '') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'company_name' => 'Company name is required.',
                ]);
            }

            $tenant = Tenant::create([
                'name' => $companyName,
                'slug' => $this->uniqueSlug($companyName),
                'is_active' => true,
            ]);

            $user->attachTenant($tenant->id, $user->tenant_id === null);
            $tenantId = $tenant->id;
        }

        if (!$tenantId) {
            Auth::logout();
            throw ValidationException::withMessages([
                'tenant_id' => 'Please select a company.',
            ]);
        }

        if ($user->hasRole('Super Admin') && !$user->canAccessTenant((int) $tenantId)) {
            $user->attachTenant((int) $tenantId, $user->tenant_id === null);
        }

        if (!$user->canAccessTenant((int) $tenantId)) {
            Auth::logout();
            throw ValidationException::withMessages([
                'tenant_id' => 'You are not assigned to this company.',
            ]);
        }

        TenantContext::set((int) $tenantId);

        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
public function destroy(Request $request): RedirectResponse
{
    auth('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();
    TenantContext::clear();

    // Redirect to homepage after admin logout
    return redirect('/');
}

    protected function ensureDefaultTenant(): Tenant
    {
        $name = 'Rahman Tiles and Sanitary';
        $slug = Str::slug($name);

        return Tenant::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'is_active' => true]
        );
    }

    protected function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
