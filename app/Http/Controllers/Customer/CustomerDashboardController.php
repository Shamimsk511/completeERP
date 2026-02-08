<?php

// Updated Customer Dashboard Controller (Return Views Instead of JSON)
// File: app/Http/Controllers/Customer/CustomerDashboardController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Cache;

class CustomerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    public function index()
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return redirect()->route('customer.login')
                    ->with('error', 'Please log in to access your dashboard.');
            }

            if (config('perf.enabled')) {
                $cacheKey = 'customer.dashboard.' . $customer->id;
                $stats = Cache::remember($cacheKey, config('perf.ttl.customer_dashboard'), function () use ($customer) {
                    return [
                        'total_invoices' => $this->safeCount($customer, 'invoices'),
                        'pending_amount' => $customer->outstanding_balance ?? 0,
                        'total_paid' => $this->safeSumTransactions($customer, 'debit'),
                        'recent_transactions' => $this->safeGetRecent($customer, 'transactions'),
                        'recent_invoices' => $this->safeGetRecent($customer, 'invoices'),
                    ];
                });
            } else {
                // Get stats with error handling
                $stats = [
                    'total_invoices' => $this->safeCount($customer, 'invoices'),
                    'pending_amount' => $customer->outstanding_balance ?? 0,
                    'total_paid' => $this->safeSumTransactions($customer, 'debit'),
                    'recent_transactions' => $this->safeGetRecent($customer, 'transactions'),
                    'recent_invoices' => $this->safeGetRecent($customer, 'invoices'),
                ];
            }

            // Return view instead of JSON
            return view('customer.dashboard', compact('customer', 'stats'));

        } catch (Exception $e) {
            \Log::error('Customer dashboard error: ' . $e->getMessage());
            
            return redirect()->route('customer.login')
                ->with('error', 'Dashboard error occurred. Please try again.');
        }
    }

    public function ledger(Request $request)
{
    try {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('customer.login');
        }

        // Start the query
        $query = $customer->transactions()->latest();

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        // Paginate results
        $transactions = $query->paginate(50);

        return view('customer.ledger', compact('customer', 'transactions'));

    } catch (Exception $e) {
        \Log::error('Customer ledger error: ' . $e->getMessage());
        
        return redirect()->route('customer.dashboard')
            ->with('error', 'Ledger error occurred. Please try again.');
    }
}
    public function invoices(Request $request)
{
    try {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('customer.login');
        }

        // Start the query
        $query = $customer->invoices()->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('payment_status', $request->input('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('to_date'));
        }

        // Paginate results
        $invoices = $query->paginate(25);

        return view('customer.invoices', compact('customer', 'invoices'));

    } catch (Exception $e) {
        \Log::error('Customer invoices error: ' . $e->getMessage());
        
        return redirect()->route('customer.dashboard')
            ->with('error', 'Invoices error occurred. Please try again.');
    }
}

public function invoiceDetails($invoiceId)
{
    try {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('customer.login')->with('error', 'Please log in.');
        }

        $invoice = $customer->invoices()->with([
            'customer:id,name,phone,address',
            'items.product.category:id,name',
            'items.product.company:id,name'
        ])->findOrFail($invoiceId);

        return view('customer.invoice-details', compact('invoice'));

    } catch (Exception $e) {
        \Log::error('Customer invoice details error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error loading invoice details.');
    }
}
    public function profile()
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return redirect()->route('customer.login');
            }

            return view('customer.profile', compact('customer'));

        } catch (Exception $e) {
            \Log::error('Customer profile error: ' . $e->getMessage());
            
            return redirect()->route('customer.dashboard')
                ->with('error', 'Profile error occurred. Please try again.');
        }
    }

    // Helper methods (same as before)
    private function safeCount($customer, $relation)
    {
        try {
            if (method_exists($customer, $relation)) {
                return $customer->$relation()->count();
            }
            return 0;
        } catch (Exception $e) {
            \Log::warning("Failed to count {$relation}: " . $e->getMessage());
            return 0;
        }
    }

    private function safeSumTransactions($customer, $type)
    {
        try {
            if (method_exists($customer, 'transactions')) {
                return $customer->transactions()->where('type', $type)->sum('amount') ?? 0;
            }
            return 0;
        } catch (Exception $e) {
            \Log::warning("Failed to sum transactions: " . $e->getMessage());
            return 0;
        }
    }

    private function safeGetRecent($customer, $relation, $limit = 5)
    {
        try {
            if (method_exists($customer, $relation)) {
                return $customer->$relation()->latest()->take($limit)->get();
            }
            return collect();
        } catch (Exception $e) {
            \Log::warning("Failed to get recent {$relation}: " . $e->getMessage());
            return collect();
        }
    }
}

?>
