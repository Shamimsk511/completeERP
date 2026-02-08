<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Payee;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
        $this->middleware('permission:account-list', ['only' => ['index', 'show', 'ledger', 'data']]);
        $this->middleware('permission:account-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:account-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:account-delete', ['only' => ['destroy']]);
    }

    /**
     * Display list of accounts (ledgers)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->data($request);
        }

        $groups = AccountGroup::orderBy('name')->get();
        $accountTypes = [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'employee' => 'Employee',
            'expense' => 'Expense',
            'income' => 'Income',
            'asset' => 'Asset',
            'liability' => 'Liability',
            'capital' => 'Capital',
            'suspense' => 'Suspense',
        ];

        return view('accounting.accounts.index', compact('groups', 'accountTypes'));
    }

    /**
     * DataTable data source
     */
    public function data(Request $request)
    {
        $query = Account::with('accountGroup')->select('accounts.*');

        // Apply filters
        if ($request->filled('account_group_id')) {
            $query->where('account_group_id', $request->account_group_id);
        }

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
        }

        return DataTables::of($query)
            ->addColumn('group_name', fn($account) => $account->accountGroup->name ?? '-')
            ->addColumn('balance', function ($account) {
                $balance = $account->running_balance;
                $symbol = $balance['balance_type'] === 'debit' ? 'Dr' : 'Cr';
                return '৳' . number_format($balance['balance'], 2) . ' ' . $symbol;
            })
            ->addColumn('status', function ($account) {
                return $account->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($account) {
                return view('accounting.accounts.partials.actions', compact('account'))->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * Show form for creating a new account
     */
    public function create()
    {
        $groups = AccountGroup::with('parent')->orderBy('name')->get();
        $accountTypes = [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'employee' => 'Employee',
            'expense' => 'Expense',
            'income' => 'Income',
            'asset' => 'Asset',
            'liability' => 'Liability',
            'capital' => 'Capital',
            'suspense' => 'Suspense',
        ];

        return view('accounting.accounts.create', compact('groups', 'accountTypes'));
    }

    /**
     * Store a new account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:accounts,code',
            'account_group_id' => 'required|exists:account_groups,id',
            'account_type' => 'required|in:cash,bank,customer,supplier,employee,expense,income,asset,liability,capital,suspense',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required_with:opening_balance|in:debit,credit',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['current_balance_type'] = $validated['opening_balance_type'] ?? 'debit';
        $validated['is_active'] = $request->has('is_active');

        Account::create($validated);

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display a specific account
     */
    public function show(Account $account)
    {
        $account->load('accountGroup');

        return view('accounting.accounts.show', compact('account'));
    }

    /**
     * Show form for editing an account
     */
    public function edit(Account $account)
    {
        if ($account->is_system) {
            return redirect()->route('accounting.accounts.index')
                ->with('error', 'System accounts cannot be edited.');
        }

        $groups = AccountGroup::with('parent')->orderBy('name')->get();
        $accountTypes = [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'employee' => 'Employee',
            'expense' => 'Expense',
            'income' => 'Income',
            'asset' => 'Asset',
            'liability' => 'Liability',
            'capital' => 'Capital',
            'suspense' => 'Suspense',
        ];

        return view('accounting.accounts.edit', compact('account', 'groups', 'accountTypes'));
    }

    /**
     * Update an account
     */
    public function update(Request $request, Account $account)
    {
        if ($account->is_system) {
            return redirect()->route('accounting.accounts.index')
                ->with('error', 'System accounts cannot be modified.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:accounts,code,' . $account->id,
            'account_group_id' => 'required|exists:account_groups,id',
            'account_type' => 'required|in:cash,bank,customer,supplier,employee,expense,income,asset,liability,capital,suspense',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required_with:opening_balance|in:debit,credit',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $account->update($validated);

        // Recalculate balance if opening balance changed
        $this->glService->updateAccountBalance($account);

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Delete an account
     */
    public function destroy(Account $account)
    {
        if (!$account->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this account. It may be a system account or have ledger entries.',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }

    /**
     * Display account ledger
     */
    public function ledger(Request $request, Account $account)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());

        $ledger = $this->glService->getAccountLedger($account, $fromDate, $toDate);

        return view('accounting.accounts.ledger', compact('account', 'ledger', 'fromDate', 'toDate'));
    }

    /**
     * Print account ledger
     */
    public function printLedger(Request $request, Account $account)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());

        $ledger = $this->glService->getAccountLedger($account, $fromDate, $toDate);

        return view('accounting.accounts.ledger-print', compact('account', 'ledger', 'fromDate', 'toDate'));
    }

    /**
     * Get linked account for a customer (API endpoint)
     */
    public function getCustomerAccount($customerId)
    {
        $account = Account::where('linkable_type', 'customer')
            ->where('linkable_id', $customerId)
            ->first();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'No linked account found',
            ]);
        }

        $balance = $account->running_balance;

        return response()->json([
            'success' => true,
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'code' => $account->code,
                'balance' => $balance['balance'],
                'balance_type' => $balance['balance_type'],
                'ledger_url' => route('accounting.accounts.ledger', $account),
            ],
        ]);
    }

    /**
     * Sync all existing customers and companies to their ledger accounts
     */
    public function syncCustomersAndCompanies(Request $request)
    {
        $sundryDebtors = AccountGroup::where('code', 'SUNDRY-DEBTORS')->first();
        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

        if (!$sundryDebtors || !$sundryCreditors) {
            return back()->with('error', 'Account groups not found. Please ensure Chart of Accounts is properly set up.');
        }

        $results = [
            'customers_created' => 0,
            'customers_updated' => 0,
            'companies_created' => 0,
            'companies_updated' => 0,
            'payees_created' => 0,
            'payees_updated' => 0,
        ];

        DB::beginTransaction();
        try {
            // Sync Customers to Sundry Debtors
            $customers = Customer::all();
            foreach ($customers as $customer) {
                $existingAccount = Account::where('linkable_type', 'customer')
                    ->where('linkable_id', $customer->id)
                    ->first();

                if ($existingAccount) {
                    if ($existingAccount->name !== $customer->name) {
                        $existingAccount->update(['name' => $customer->name]);
                        $results['customers_updated']++;
                    }
                } else {
                    $openingBalance = $customer->opening_balance ?? 0;
                    Account::create([
                        'name' => $customer->name,
                        'code' => 'CUST-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
                        'account_group_id' => $sundryDebtors->id,
                        'account_type' => 'customer',
                        'opening_balance' => $openingBalance,
                        'opening_balance_type' => 'debit',
                        'current_balance' => $openingBalance,
                        'current_balance_type' => 'debit',
                        'linkable_type' => 'customer',
                        'linkable_id' => $customer->id,
                        'is_active' => true,
                        'is_system' => false,
                        'notes' => 'Auto-synced from customer',
                    ]);
                    $results['customers_created']++;
                }
            }

            // Sync Companies to Sundry Creditors
            $companies = Company::all();
            foreach ($companies as $company) {
                $existingAccount = Account::where('linkable_type', 'company')
                    ->where('linkable_id', $company->id)
                    ->first();

                if ($existingAccount) {
                    if ($existingAccount->name !== $company->name) {
                        $existingAccount->update(['name' => $company->name]);
                        $results['companies_updated']++;
                    }
                } else {
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
                        'notes' => 'Auto-synced from vendor/company',
                    ]);
                    $results['companies_created']++;
                }
            }

            // Sync Payees to Sundry Creditors
            $payees = Payee::all();
            foreach ($payees as $payee) {
                $existingAccount = Account::where('linkable_type', 'payee')
                    ->where('linkable_id', $payee->id)
                    ->first();

                if ($existingAccount) {
                    if ($existingAccount->name !== $payee->name) {
                        $existingAccount->update(['name' => $payee->name]);
                        $results['payees_updated']++;
                    }
                } else {
                    $openingBalance = $payee->opening_balance ?? 0;
                    Account::create([
                        'name' => $payee->name,
                        'code' => 'PAY-' . str_pad($payee->id, 5, '0', STR_PAD_LEFT),
                        'account_group_id' => $sundryCreditors->id,
                        'account_type' => 'supplier',
                        'opening_balance' => $openingBalance,
                        'opening_balance_type' => 'credit',
                        'current_balance' => $openingBalance,
                        'current_balance_type' => 'credit',
                        'linkable_type' => 'payee',
                        'linkable_id' => $payee->id,
                        'is_active' => true,
                        'is_system' => false,
                        'notes' => 'Auto-synced from payee',
                    ]);
                    $results['payees_created']++;
                }
            }

            DB::commit();

            $message = "Sync completed! Customers: {$results['customers_created']} created, {$results['customers_updated']} updated. Companies: {$results['companies_created']} created, {$results['companies_updated']} updated. Payees: {$results['payees_created']} created, {$results['payees_updated']} updated.";
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Clear all caches (response cache, application cache, views, config)
     */
    public function clearCache(Request $request)
    {
        try {
            // Clear application cache
            \Illuminate\Support\Facades\Cache::flush();

            // Clear compiled views
            $viewPath = storage_path('framework/views');
            if (is_dir($viewPath)) {
                $files = glob($viewPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            return back()->with('success', 'Cache cleared successfully! Pages will now load fresh data.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cache clear failed: ' . $e->getMessage());
        }
    }
}
