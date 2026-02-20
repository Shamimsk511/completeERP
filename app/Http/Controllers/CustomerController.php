<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\InvoiceItem;
use App\Models\ProductReturnItem;
use App\Models\Category;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Illuminate\Http\Request;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersTemplateExport;
use Illuminate\Support\Facades\Validator;
use App\Services\TransactionSmsService;
use App\Services\PaymentAllocationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\TenantContext;

class CustomerController extends Controller
{
    protected $transactionSmsService;
    protected PaymentAllocationService $paymentAllocationService;

    public function __construct(TransactionSmsService $transactionSmsService, PaymentAllocationService $paymentAllocationService)
    {
        $this->transactionSmsService = $transactionSmsService;
        $this->paymentAllocationService = $paymentAllocationService;

        $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete', ['only' => ['index','show','getCustomers','searchCustomers','checkDuplicate','purchaseSummaryData','returnItemsData']]);
        $this->middleware('permission:customer-create|customer-import', ['only' => ['create','store', 'importForm', 'import', 'exportTemplate']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit','update','showLoginInfo','sendSms']]);
        $this->middleware('permission:customer-export', ['only' => ['export']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy']]);
        $this->middleware('permission:customer-ledger', ['only' => ['printLedger']]);
    }

    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        // Get customer groups for the filter dropdown
        $customerGroups = $this->getCustomerAccountGroups();

        return view('customers.index', compact('customerGroups'));
    }

    /**
     * Process DataTables ajax request.
     */
    public function getCustomers(Request $request)
    {
        if ($request->ajax()) {
            $query = Customer::query()
                ->select([
                    'id',
                    'name',
                    'phone',
                    'address',
                    'opening_balance',
                    'outstanding_balance',
                    'account_group_id',
                    'created_at',
                ])
                ->with('accountGroup:id,name');

            // Handle custom search input
            if ($request->has('search_input') && !empty($request->search_input)) {
                $searchTerm = $request->search_input;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('address', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Handle customer group filter
            if ($request->has('group_filter') && !empty($request->group_filter)) {
                $query->where('account_group_id', $request->group_filter);
            }

            // Handle balance filter
            if ($request->has('balance_filter') && !empty($request->balance_filter)) {
                $balanceFilter = $request->balance_filter;
                switch ($balanceFilter) {
                    case 'positive':
                        $query->where('outstanding_balance', '>', 0);
                        break;
                    case 'zero':
                        $query->where('outstanding_balance', '=', 0);
                        break;
                    case 'negative':
                        $query->where('outstanding_balance', '<', 0);
                        break;
                    case 'high':
                        $query->where('outstanding_balance', '>', 5000);
                        break;
                }
            }

            // Handle sorting
            if ($request->has('sort_filter') && !empty($request->sort_filter)) {
                $sortFilter = $request->sort_filter;
                switch ($sortFilter) {
                    case 'name_asc':
                        $query->orderBy('name', 'asc');
                        break;
                    case 'name_desc':
                        $query->orderBy('name', 'desc');
                        break;
                    case 'balance_desc':
                        $query->orderBy('outstanding_balance', 'desc');
                        break;
                    case 'balance_asc':
                        $query->orderBy('outstanding_balance', 'asc');
                        break;
                    case 'recent':
                        $query->orderBy('created_at', 'desc');
                        break;
                    default:
                        $query->orderBy('name', 'asc');
                }
            }

            $summaryQuery = (clone $query)->setEagerLoads([])->reorder();
            $summaryQuery->getQuery()->columns = null;
            $summary = $summaryQuery->selectRaw('COUNT(*) as total_customers')
                ->selectRaw('COALESCE(SUM(outstanding_balance), 0) as total_outstanding')
                ->selectRaw('SUM(CASE WHEN outstanding_balance > 5000 THEN 1 ELSE 0 END) as high_outstanding')
                ->selectRaw('SUM(CASE WHEN outstanding_balance <> 0 THEN 1 ELSE 0 END) as active_customers')
                ->first();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('account_group_name', function($row) {
                    return $row->accountGroup ? $row->accountGroup->name : null;
                })
                ->addColumn('action', function($row) {
                    $actionBtn = '<a href="'.route('customers.edit', $row->id).'" class="edit btn btn-primary btn-sm">Edit</a> ';
                    $actionBtn .= '<a href="'.route('customers.show', $row->id).'" class="show btn btn-info btn-sm">View</a> ';
                    $actionBtn .= '<a href="'.route('customers.ledger', $row->id).'" class="show btn btn-success btn-sm">Ledger</a> ';
                    $actionBtn .= '<form action="'.route('customers.destroy', $row->id).'" method="POST" style="display:inline">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->with([
                    'summary' => $summary,
                ])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        // Get customer groups (Sundry Debtors and its sub-groups)
        $customerGroups = $this->getCustomerAccountGroups();

        return view('customers.create', compact('customerGroups'));
    }

    /**
     * Check for duplicate customer name or phone (AJAX).
     */
    public function checkDuplicate(Request $request)
    {
        $name = trim((string) $request->get('name', ''));
        $phone = trim((string) $request->get('phone', ''));
        $excludeId = $request->get('exclude_id');

        if ($name === '' && $phone === '') {
            return response()->json([
                'exists' => false,
                'message' => ''
            ]);
        }

        $normalizedName = $name !== '' ? Str::lower(trim(preg_replace('/\s+/', ' ', $name))) : null;

        $query = Customer::query();
        $query->where(function ($q) use ($normalizedName, $phone) {
            if ($normalizedName) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName]);
            }

            if ($phone) {
                $q->orWhere('phone', $phone);
            }
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingCustomers = $query->orderBy('name')->limit(5)->get(['id', 'name', 'phone', 'address', 'outstanding_balance']);

        if ($existingCustomers->isNotEmpty()) {
            return response()->json([
                'exists' => true,
                'count' => $existingCustomers->count(),
                'message' => 'Customer with this name or phone already exists.',
                'customers' => $existingCustomers
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => ''
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
 public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('customers', 'name')],
        'phone' => ['required', 'string', 'max:20', $this->tenantUniqueRule('customers', 'phone')],
        'address' => 'nullable|string',
        'opening_balance' => 'nullable|numeric',
        'account_group_id' => 'nullable|exists:account_groups,id',
    ]);

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $customerData = $request->only(['name', 'phone', 'address', 'opening_balance', 'account_group_id']);
    $customerData['outstanding_balance'] = $customerData['opening_balance'] ?? 0;

    $customer = Customer::create($customerData);

    // Create ledger account for customer (handled by Observer)
    // $this->createCustomerLedgerAccount($customer); // Observer handles this now

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'customer' => $customer->load('accountGroup')
        ]);
    }

    return redirect()->route('customers.index')
        ->with('success', 'Customer created successfully.');
}


    /**
     * Display the specified customer.
     */
public function show(Customer $customer)
{
    $customer = Customer::with([
        'invoices' => function ($query) {
            $query->select([
                    'id',
                    'invoice_number',
                    'invoice_date',
                    'customer_id',
                    'total',
                    'paid_amount',
                    'due_amount',
                    'payment_status',
                ])
                ->withCount('items')
                ->withSum('items', 'quantity')
                ->latest('invoice_date');
        },
        'productReturns' => function ($query) {
            $query->select([
                    'id',
                    'return_number',
                    'return_date',
                    'customer_id',
                    'invoice_id',
                    'total',
                    'status',
                ])
                ->with('invoice:id,invoice_number')
                ->withCount('items')
                ->withSum('items', 'quantity')
                ->latest('return_date');
        },
        'challans' => function ($query) {
            $query->select([
                    'challans.id as id',
                    'challans.challan_number',
                    'challans.challan_date',
                    'challans.invoice_id',
                ])
                ->with('invoice:id,invoice_number')
                ->withCount('items')
                ->withSum('items', 'quantity')
                ->latest('challan_date');
        },
    ])->findOrFail($customer->id);

    $invoiceStats = Invoice::where('customer_id', $customer->id)
        ->selectRaw('COUNT(*) as invoice_count')
        ->selectRaw('COALESCE(SUM(total), 0) as total_sales')
        ->selectRaw('COALESCE(AVG(total), 0) as avg_basket')
        ->selectRaw('MIN(invoice_date) as first_date')
        ->selectRaw('MAX(invoice_date) as last_date')
        ->first();

    $invoiceCount = (int) ($invoiceStats->invoice_count ?? 0);
    $totalSales = (float) ($invoiceStats->total_sales ?? 0);
    $avgBasket = (float) ($invoiceStats->avg_basket ?? 0);
    $firstDate = $invoiceStats->first_date ? Carbon::parse($invoiceStats->first_date) : null;
    $lastDate = $invoiceStats->last_date ? Carbon::parse($invoiceStats->last_date) : null;

    $monthsSpan = 0;
    if ($firstDate && $lastDate) {
        $monthsSpan = max(1, $firstDate->diffInMonths($lastDate) + 1);
    }

    $visitsPerMonth = $monthsSpan > 0 ? round($invoiceCount / $monthsSpan, 2) : 0;
    $daysSinceLast = $lastDate ? $lastDate->diffInDays(Carbon::today()) : null;

    $lastInvoice = $lastDate
        ? Invoice::where('customer_id', $customer->id)
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc')
            ->first(['invoice_number', 'invoice_date', 'total'])
        : null;

    $customerInsights = [
        'invoice_count' => $invoiceCount,
        'total_sales' => $totalSales,
        'avg_basket' => $avgBasket,
        'visits_per_month' => $visitsPerMonth,
        'last_date' => $lastDate,
        'days_since_last' => $daysSinceLast,
        'last_invoice' => $lastInvoice,
    ];

    $categories = Category::orderBy('name')->get(['id', 'name']);

    // Add username info for admin
    $customer->login_username = $customer->username;
    
    return view('customers.show', compact('customer', 'customerInsights', 'categories'));
}

public function purchaseSummaryData(Request $request, Customer $customer)
{
    $tenantId = $customer->tenant_id ?: TenantContext::currentId();

    $query = DB::table('invoice_items')
        ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
        ->leftJoin('products', 'invoice_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->where('invoices.customer_id', $customer->id)
        ->selectRaw('invoice_items.product_id')
        ->selectRaw('COALESCE(products.name, "Unknown Product") as product_name')
        ->selectRaw('COALESCE(categories.name, "Uncategorized") as category_name')
        ->selectRaw('SUM(invoice_items.quantity) as total_quantity')
        ->selectRaw('SUM(invoice_items.total) as total_amount')
        ->selectRaw('MAX(invoices.invoice_date) as last_purchase_date')
        ->groupBy('invoice_items.product_id', 'products.name', 'categories.name');

    if (!empty($tenantId)) {
        $query->where(function ($q) use ($tenantId) {
            $q->where('invoices.tenant_id', $tenantId)
                ->orWhereNull('invoices.tenant_id');
        });
    }

    if ($request->filled('category_id')) {
        $categoryId = $request->input('category_id');
        if ((string) $categoryId === '0') {
            $query->whereNull('products.category_id');
        } else {
            $query->where('products.category_id', $categoryId);
        }
    }

    return DataTables::of($query)
        ->editColumn('total_quantity', fn($row) => number_format($row->total_quantity, 2))
        ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
        ->editColumn('last_purchase_date', function ($row) {
            return $row->last_purchase_date
                ? Carbon::parse($row->last_purchase_date)->format('d M, Y')
                : '-';
        })
        ->make(true);
}

public function returnItemsData(Request $request, Customer $customer)
{
    $tenantId = $customer->tenant_id ?: TenantContext::currentId();

    $query = DB::table('product_return_items')
        ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
        ->leftJoin('products', 'product_return_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->leftJoin('invoices', 'product_returns.invoice_id', '=', 'invoices.id')
        ->where('product_returns.customer_id', $customer->id)
        ->select([
            'product_return_items.id',
            'product_returns.id as return_id',
            'product_returns.return_number',
            'product_returns.return_date',
            'product_returns.invoice_id',
            'invoices.invoice_number',
            'products.name as product_name',
            DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
            'product_return_items.quantity',
            'product_return_items.total',
        ]);

    if (!empty($tenantId)) {
        $query->where(function ($q) use ($tenantId) {
            $q->where('product_returns.tenant_id', $tenantId)
                ->orWhereNull('product_returns.tenant_id');
        });
    }

    return DataTables::of($query)
        ->editColumn('quantity', fn($row) => number_format($row->quantity, 2))
        ->editColumn('total', fn($row) => number_format($row->total, 2))
        ->editColumn('return_date', function ($row) {
            return $row->return_date
                ? Carbon::parse($row->return_date)->format('d M, Y')
                : '-';
        })
        ->make(true);
}

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        // Get customer groups (Sundry Debtors and its sub-groups)
        $customerGroups = $this->getCustomerAccountGroups();

        return view('customers.edit', compact('customer', 'customerGroups'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('customers', 'name', $customer->id)],
            'phone' => ['required', 'string', 'max:20', $this->tenantUniqueRule('customers', 'phone', $customer->id)],
            'address' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'account_group_id' => 'nullable|exists:account_groups,id',
        ]);

        $newOpeningBalance = $request->input('opening_balance', 0);
        $oldOpeningBalance = $customer->opening_balance ?? 0;

        // Calculate the difference in opening balance
        $balanceChange = $newOpeningBalance - $oldOpeningBalance;

        // Update customer record (Observer will handle ledger account sync)
        $customer->update([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'opening_balance' => $newOpeningBalance,
            'account_group_id' => $request->input('account_group_id'),
        ]);

        // Sync ledger account name and balance
        $this->syncCustomerLedgerAccount($customer, $balanceChange);

        $this->paymentAllocationService->allocatePayments($customer->id);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        $hasActiveInvoices = Invoice::where('customer_id', $customer->id)->exists();
        $hasActiveTransactions = Transaction::where('customer_id', $customer->id)->exists();

        if ($hasActiveInvoices || $hasActiveTransactions) {
            return redirect()->route('customers.index')
                ->with('error', 'Customer cannot be moved to trash while active invoices or transactions exist.');
        }

        $customer->deleted_by = auth()->id();
        $customer->save();
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer moved to trash successfully.');
    }

    public function printLedger(Customer $customer)
{
    $transactions = Transaction::where('customer_id', $customer->id)
                              ->orderBy('created_at', 'asc')
                              ->get();
    
    return view('customers.print_ledger', compact('customer', 'transactions'));
}

public function importForm()
{
    return view('customers.import');
}

/**
 * Process Excel import
 */
public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
    ]);

    try {
        Excel::import(new CustomersImport, $request->file('file'));
        return redirect()->route('customers.index')
            ->with('success', 'Customers imported successfully.');
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error importing customers: ' . $e->getMessage());
    }
}

/**
 * Download sample Excel template
 */
public function exportTemplate()
{
    return Excel::download(new CustomersTemplateExport, 'customers_template.xlsx');
}
public function export(Request $request)
{
    $selectedIds = $request->get('selected');
    
    if ($selectedIds) {
        $ids = explode(',', $selectedIds);
        $customers = Customer::whereIn('id', $ids)->get();
        return Excel::download(new CustomersExport($customers), 'selected_customers.xlsx');
    } else {
        return Excel::download(new CustomersExport(), 'all_customers.xlsx');
    }
}
public function showLoginInfo(Customer $customer)
{
    $hasPassword = !empty($customer->password);
    return response()->json([
        'customer_id' => $customer->id,
        'name' => $customer->name,
        'username' => $customer->username,
        'phone' => $customer->phone,
        'can_login' => !empty($customer->phone),
        'password_set' => $hasPassword,
        'login_url' => route('customer.login'),
        'instructions' => [
            'username' => 'Use: ' . $customer->username,
            'password' => $hasPassword ? 'Use the customer-set password' : 'Use customer phone number',
            'example' => $hasPassword
                ? "Username: {$customer->username}, Password: [customer-set]"
                : "Username: {$customer->username}, Password: {$customer->phone}"
        ]
    ]);
}

// Replace the sendSms method in your CustomerController with this updated version

public function sendSms(Customer $customer, Request $request)
{
    try {
        // Add debug logging
        Log::info("SMS request started for customer {$customer->id}", [
            'customer_phone' => $customer->phone,
            'customer_balance' => $customer->outstanding_balance,
            'request_data' => $request->all()
        ]);

        // Use the new TransactionSmsService for validation and sending
        $result = $this->transactionSmsService->sendReminderSms($customer);
        
        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'provider' => $result['provider'] ?? 'unknown'
            ]);
        } else {
            $statusCode = 422;
            
            // Determine appropriate status code
            if (strpos($result['message'], 'recently') !== false) {
                $statusCode = 429; // Too Many Requests
            } elseif (strpos($result['message'], 'phone') !== false || 
                      strpos($result['message'], 'balance') !== false) {
                $statusCode = 422; // Unprocessable Entity
            } else {
                $statusCode = 500; // Internal Server Error
            }
            
            return response()->json([
                'message' => $result['message']
            ], $statusCode);
        }

    } catch (\Exception $e) {
        Log::error("Error sending reminder SMS for customer {$customer->id}: " . $e->getMessage(), [
            'phone' => $customer->phone ?? 'N/A',
            'stack' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'message' => 'An error occurred while sending SMS.',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

    /**
     * Sync ledger account when customer is updated
     */
    protected function syncCustomerLedgerAccount(Customer $customer, float $balanceChange): void
    {
        $account = Account::where('linkable_type', 'customer')
            ->where('linkable_id', $customer->id)
            ->first();

        if (!$account) {
            // Account will be created by Observer
            return;
        }

        // Determine account group
        $accountGroupId = $customer->account_group_id;
        if (!$accountGroupId) {
            $sundryDebtors = AccountGroup::where('code', 'SUNDRY-DEBTORS')->first();
            $accountGroupId = $sundryDebtors?->id;
        }

        // Update account name, group, and balance
        $newBalance = $account->current_balance + $balanceChange;
        $newOpeningBalance = $account->opening_balance + $balanceChange;

        $account->update([
            'name' => $customer->name,
            'account_group_id' => $accountGroupId,
            'opening_balance' => $newOpeningBalance,
            'current_balance' => $newBalance,
        ]);
    }

    /**
     * Get customer account groups (Sundry Debtors and its sub-groups)
     */
    protected function getCustomerAccountGroups()
    {
        // Get Sundry Debtors group
        $sundryDebtors = AccountGroup::where('code', 'SUNDRY-DEBTORS')->first();

        if (!$sundryDebtors) {
            return collect();
        }

        // Get Sundry Debtors and all its descendants
        $groups = collect([$sundryDebtors]);

        // Recursively get all child groups
        $this->getChildGroups($sundryDebtors, $groups);

        return $groups;
    }

    /**
     * Recursively get child groups
     */
    protected function getChildGroups(AccountGroup $parent, &$groups): void
    {
        foreach ($parent->children as $child) {
            $groups->push($child);
            $this->getChildGroups($child, $groups);
        }
    }
}
