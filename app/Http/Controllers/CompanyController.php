<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payee;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Models\PayableTransaction;
use App\Models\Product;
use App\Services\Accounting\OpeningBalanceService;
use App\Services\PayeeAccountService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::with('ledgerAccount')->latest()->get();
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $name = trim((string) $request->input('name', ''));
        if ($name !== '') {
            $existing = Company::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            if ($existing) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'id' => $existing->id,
                        'name' => $existing->name,
                        'was_existing' => true,
                    ]);
                }
                return back()->withErrors(['name' => 'Company already exists.'])->withInput();
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('companies', 'name')],
            'description' => 'nullable|string',
            'contact' => 'nullable|string|max:255',
            'type' => 'required|in:supplier,brand,both',
            'opening_balance' => 'nullable|numeric',
            'opening_balance_direction' => 'nullable|in:we_owe,they_owe',
        ]);

        $openingDirection = $validated['opening_balance_direction'] ?? 'we_owe';
        $validated['opening_balance_type'] = $openingDirection === 'they_owe' ? 'debit' : 'credit';
        unset($validated['opening_balance_direction']);

        $company = Company::create($validated);

        // Create ledger account for company in Sundry Creditors
        $this->createCompanyLedgerAccount($company);

        if ($company->isSupplierType()) {
            $payee = Payee::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'name' => $company->name,
                    'type' => 'supplier',
                    'category' => 'supplier',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                ]
            );
            app(PayeeAccountService::class)->ensureAccountForPayee($payee);
        }

        if ($company->isSupplierType() && !empty($company->opening_balance)) {
            app(OpeningBalanceService::class)->postCompanyOpeningBalance(
                $company,
                (float) $company->opening_balance,
                $company->opening_balance_type ?: 'credit',
                now()->toDateString(),
                auth()->id()
            );
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $company->id,
                'name' => $company->name,
                'was_existing' => false,
            ]);
        }

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        $company->load('ledgerAccount');
        return view('companies.show', compact('company'));
    }

    public function recentPaymentsData(Request $request, Company $company)
    {
        $payee = Payee::where('company_id', $company->id)->first();

        if (!$payee) {
            return DataTables::of(collect())->make(true);
        }

        $query = PayableTransaction::query()
            ->where('payee_id', $payee->id)
            ->select('id', 'transaction_date', 'transaction_type', 'payment_method', 'amount');

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->editColumn('transaction_date', function ($row) {
                return optional($row->transaction_date)->format('d M Y');
            })
            ->editColumn('transaction_type', function ($row) {
                return ucwords(str_replace('_', ' ', $row->transaction_type ?? ''));
            })
            ->editColumn('payment_method', function ($row) {
                return $row->payment_method ?? 'N/A';
            })
            ->addColumn('amount_formatted', function ($row) {
                return '৳' . number_format((float) ($row->amount ?? 0), 2);
            })
            ->make(true);
    }

    public function topProductsData(Request $request, Company $company)
    {
        $tenantId = TenantContext::currentId();

        $query = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(purchase_items.quantity) as total_qty')
            )
            ->where('purchases.company_id', $company->id)
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('purchase_items.tenant_id', $tenantId)
                    ->where('purchases.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId);
            })
            ->groupBy('products.id', 'products.name');

        if ($request->filled('start_date')) {
            $query->whereDate('purchases.purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchases.purchase_date', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->addColumn('total_qty_formatted', function ($row) {
                return number_format((float) ($row->total_qty ?? 0), 2);
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->where('products.name', 'like', "%{$keyword}%");
            })
            ->make(true);
    }

    public function recentPurchasesData(Request $request, Company $company)
    {
        $tenantId = TenantContext::currentId();

        $query = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->select(
                'purchase_items.id',
                'purchase_items.quantity',
                'purchases.purchase_date',
                'products.id as product_id',
                'products.name as product_name'
            )
            ->where('purchases.company_id', $company->id)
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('purchase_items.tenant_id', $tenantId)
                    ->where('purchases.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId);
            });

        if ($request->filled('start_date')) {
            $query->whereDate('purchases.purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchases.purchase_date', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->editColumn('purchase_date', function ($row) {
                return $row->purchase_date ? \Carbon\Carbon::parse($row->purchase_date)->format('d M Y') : 'N/A';
            })
            ->addColumn('quantity_formatted', function ($row) {
                return number_format((float) ($row->quantity ?? 0), 2);
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->where('products.name', 'like', "%{$keyword}%");
            })
            ->make(true);
    }

    public function lowStockProductsData(Request $request, Company $company)
    {
        $query = Product::query()
            ->where('company_id', $company->id)
            ->where('is_stock_managed', true)
            ->where('current_stock', '<=', 10)
            ->select('id as product_id', 'name as product_name', 'current_stock');

        return DataTables::of($query)
            ->addColumn('current_stock_formatted', function ($row) {
                return number_format((float) ($row->current_stock ?? 0), 2);
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->make(true);
    }

    public function remainingProductsData(Request $request, Company $company)
    {
        $tenantId = TenantContext::currentId();
        $deliveredSubquery = '(SELECT invoice_item_id, SUM(quantity) as delivered_qty
                                FROM challan_items'
            . ($tenantId ? ' WHERE tenant_id = ' . (int) $tenantId : '')
            . ' GROUP BY invoice_item_id) as delivered';

        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin(DB::raw($deliveredSubquery), 'invoice_items.id', '=', 'delivered.invoice_item_id')
            ->select(
                'invoice_items.id',
                'invoices.id as invoice_id',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'customers.name as customer_name',
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('invoice_items.quantity - COALESCE(delivered.delivered_qty, 0) as remaining_quantity')
            )
            ->where('products.company_id', $company->id)
            ->where('invoices.delivery_status', '!=', 'delivered')
            ->whereRaw('invoice_items.quantity > COALESCE(delivered.delivered_qty, 0)')
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId)
                    ->where('customers.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId);
            });

        if ($request->filled('start_date')) {
            $query->whereDate('invoices.invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoices.invoice_date', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->editColumn('invoice_date', function ($row) {
                return $row->invoice_date ? \Carbon\Carbon::parse($row->invoice_date)->format('d M Y') : 'N/A';
            })
            ->addColumn('remaining_quantity_formatted', function ($row) {
                return number_format((float) ($row->remaining_quantity ?? 0), 2);
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->where('customers.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('invoice_number', function ($query, $keyword) {
                $query->where('invoices.invoice_number', 'like', "%{$keyword}%");
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->where('products.name', 'like', "%{$keyword}%");
            })
            ->make(true);
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('companies', 'name', $company->id)],
            'description' => 'nullable|string',
            'contact' => 'nullable|string|max:255',
            'type' => 'required|in:supplier,brand,both',
            'opening_balance' => 'nullable|numeric',
            'opening_balance_direction' => 'nullable|in:we_owe,they_owe',
        ]);

        $openingDirection = $validated['opening_balance_direction'] ?? 'we_owe';
        $validated['opening_balance_type'] = $openingDirection === 'they_owe' ? 'debit' : 'credit';
        unset($validated['opening_balance_direction']);

        $company->update($validated);

        // Sync ledger account name
        $this->syncCompanyLedgerAccount($company);

        if ($company->isSupplierType()) {
            $payee = Payee::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'name' => $company->name,
                    'type' => 'supplier',
                    'category' => 'supplier',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                ]
            );
            if ($payee->name !== $company->name) {
                $payee->name = $company->name;
                $payee->save();
            }
            app(PayeeAccountService::class)->ensureAccountForPayee($payee);
        }

        if ($company->isSupplierType()) {
            app(OpeningBalanceService::class)->postCompanyOpeningBalance(
                $company,
                (float) ($company->opening_balance ?? 0),
                $company->opening_balance_type ?: 'credit',
                now()->toDateString(),
                auth()->id()
            );
        }

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    /**
     * Create a ledger account for the company in Sundry Creditors group
     */
    protected function createCompanyLedgerAccount(Company $company): void
    {
        if (!$company->isSupplierType()) {
            return;
        }

        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

        if (!$sundryCreditors) {
            Log::warning("Sundry Creditors account group not found. Skipping ledger creation for company: {$company->id}");
            return;
        }

        // Check if account already exists
        $existingAccount = Account::where('linkable_type', 'company')
            ->where('linkable_id', $company->id)
            ->first();

        if ($existingAccount) {
            return;
        }

        Account::create([
            'name' => $company->name,
            'code' => 'SUPP-' . str_pad($company->id, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $sundryCreditors->id,
            'account_type' => 'supplier',
            'opening_balance' => 0,
            'opening_balance_type' => 'credit',
            'current_balance' => 0,
            'current_balance_type' => 'credit',
            'linkable_type' => 'company',
            'linkable_id' => $company->id,
            'is_active' => true,
            'is_system' => false,
            'notes' => "Auto-created from vendor/company registration",
        ]);
    }

    /**
     * Sync ledger account when company is updated
     */
    protected function syncCompanyLedgerAccount(Company $company): void
    {
        if (!$company->isSupplierType()) {
            $account = Account::where('linkable_type', 'company')
                ->where('linkable_id', $company->id)
                ->first();

            if ($account) {
                $account->update(['is_active' => false]);
            }
            return;
        }

        $account = Account::where('linkable_type', 'company')
            ->where('linkable_id', $company->id)
            ->first();

        if (!$account) {
            // Create account if it doesn't exist
            $this->createCompanyLedgerAccount($company);
            return;
        }

        // Update account name
        $account->update([
            'name' => $company->name,
        ]);
    }
}
