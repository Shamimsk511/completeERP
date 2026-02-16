<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountGroup;
use Illuminate\Http\Request;

class AccountGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:account-list', ['only' => ['index', 'show', 'tree']]);
        $this->middleware('permission:account-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:account-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:account-delete', ['only' => ['destroy']]);
    }

    /**
     * Display the chart of accounts (tree view)
     */
    public function index()
    {
        $groups = AccountGroup::with(['allChildren.accounts', 'accounts'])
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get();

        // Pre-calculate group totals using efficient query
        $groupTotals = $this->calculateGroupTotals();

        return view('accounting.account-groups.index', compact('groups', 'groupTotals'));
    }

    /**
     * Calculate totals for all root groups using efficient SQL
     */
    private function calculateGroupTotals(): array
    {
        // Get all accounts with their balances in one query
        $accounts = \App\Models\Accounting\Account::select('accounts.id', 'accounts.account_group_id', 'accounts.opening_balance', 'accounts.opening_balance_type')
            ->with(['accountGroup' => function($q) {
                $q->select('id', 'parent_id', 'nature');
            }])
            ->get();

        // Get ledger entry totals per account in one query
        $entryTotals = \App\Models\Accounting\AccountEntry::select('account_id')
            ->selectRaw('SUM(debit_amount) as total_debit')
            ->selectRaw('SUM(credit_amount) as total_credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        // Build a map of group_id -> root_group_nature
        $groupNatures = [];
        $allGroups = \App\Models\Accounting\AccountGroup::select('id', 'parent_id', 'nature')->get()->keyBy('id');

        foreach ($allGroups as $group) {
            $rootNature = $group->nature;
            $parent = $group;
            while ($parent->parent_id) {
                $parent = $allGroups[$parent->parent_id] ?? null;
                if ($parent) {
                    $rootNature = $parent->nature;
                }
            }
            $groupNatures[$group->id] = $rootNature;
        }

        // Calculate totals per nature
        $natureTotals = [
            'assets' => ['debit' => 0, 'credit' => 0],
            'liabilities' => ['debit' => 0, 'credit' => 0],
            'income' => ['debit' => 0, 'credit' => 0],
            'expenses' => ['debit' => 0, 'credit' => 0],
            'capital' => ['debit' => 0, 'credit' => 0],
        ];

        foreach ($accounts as $account) {
            $nature = $groupNatures[$account->account_group_id] ?? 'assets';

            // Opening balance
            $openingDebit = $account->opening_balance_type === 'debit' ? $account->opening_balance : 0;
            $openingCredit = $account->opening_balance_type === 'credit' ? $account->opening_balance : 0;

            // Transaction totals
            $entry = $entryTotals[$account->id] ?? null;
            $txnDebit = $entry ? (float)$entry->total_debit : 0;
            $txnCredit = $entry ? (float)$entry->total_credit : 0;

            $natureTotals[$nature]['debit'] += $openingDebit + $txnDebit;
            $natureTotals[$nature]['credit'] += $openingCredit + $txnCredit;
        }

        // Format results
        $results = [];
        foreach ($natureTotals as $nature => $totals) {
            $balance = abs($totals['debit'] - $totals['credit']);
            $type = $totals['debit'] >= $totals['credit'] ? 'debit' : 'credit';
            $symbol = $type === 'debit' ? 'Dr' : 'Cr';
            $results[$nature] = $balance > 0 ? '৳' . number_format($balance, 2) . ' ' . $symbol : '৳0.00';
        }

        return $results;
    }

    /**
     * Show form for creating a new account group
     */
    public function create()
    {
        $parentGroups = AccountGroup::orderBy('name')->get();
        $natures = ['assets', 'liabilities', 'income', 'expenses', 'capital'];

        return view('accounting.account-groups.create', compact('parentGroups', 'natures'));
    }

    /**
     * Store a new account group
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', $this->tenantUniqueRule('account_groups', 'code')],
            'parent_id' => 'nullable|exists:account_groups,id',
            'nature' => 'required|in:assets,liabilities,income,expenses,capital',
            'affects_gross_profit' => 'required|in:yes,no',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        // If parent is selected, inherit nature from parent
        if ($validated['parent_id']) {
            $parent = AccountGroup::find($validated['parent_id']);
            $validated['nature'] = $parent->nature;
        }

        AccountGroup::create($validated);

        return redirect()->route('accounting.account-groups.index')
            ->with('success', 'Account group created successfully.');
    }

    /**
     * Display a specific account group
     */
    public function show(AccountGroup $accountGroup)
    {
        $accountGroup->load(['parent', 'children', 'accounts']);

        return view('accounting.account-groups.show', compact('accountGroup'));
    }

    /**
     * Show form for editing an account group
     */
    public function edit(AccountGroup $accountGroup)
    {
        if ($accountGroup->is_system) {
            return redirect()->route('accounting.account-groups.index')
                ->with('error', 'System account groups cannot be edited.');
        }

        $parentGroups = AccountGroup::where('id', '!=', $accountGroup->id)
            ->where('nature', $accountGroup->nature)
            ->orderBy('name')
            ->get();

        $natures = ['assets', 'liabilities', 'income', 'expenses', 'capital'];

        // Check if group can be deleted and get reasons if not
        $canDelete = $accountGroup->canDelete();
        $deleteReasons = [];
        if (!$canDelete) {
            if ($accountGroup->is_system) {
                $deleteReasons[] = 'This is a system group';
            }
            if ($accountGroup->accounts()->exists()) {
                $deleteReasons[] = 'Has ' . $accountGroup->accounts()->count() . ' account(s) under it';
            }
            if ($accountGroup->children()->exists()) {
                $deleteReasons[] = 'Has ' . $accountGroup->children()->count() . ' sub-group(s) under it';
            }
        }

        return view('accounting.account-groups.edit', compact('accountGroup', 'parentGroups', 'natures', 'canDelete', 'deleteReasons'));
    }

    /**
     * Update an account group
     */
    public function update(Request $request, AccountGroup $accountGroup)
    {
        if ($accountGroup->is_system) {
            return redirect()->route('accounting.account-groups.index')
                ->with('error', 'System account groups cannot be modified.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', $this->tenantUniqueRule('account_groups', 'code', $accountGroup->id)],
            'parent_id' => 'nullable|exists:account_groups,id',
            'affects_gross_profit' => 'required|in:yes,no',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        // Prevent setting itself as parent
        if ($validated['parent_id'] == $accountGroup->id) {
            return back()->withErrors(['parent_id' => 'Cannot set itself as parent.'])->withInput();
        }

        $accountGroup->update($validated);

        return redirect()->route('accounting.account-groups.index')
            ->with('success', 'Account group updated successfully.');
    }

    /**
     * Delete an account group
     */
    public function destroy(AccountGroup $accountGroup)
    {
        if (!$accountGroup->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this account group. It may be a system group or have accounts/sub-groups.',
            ], 422);
        }

        $accountGroup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account group deleted successfully.',
        ]);
    }

    /**
     * Get tree structure for AJAX requests
     */
    public function tree()
    {
        $groups = AccountGroup::with(['allChildren', 'accounts'])
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get();

        return response()->json($this->buildTree($groups));
    }

    /**
     * Build tree structure recursively
     */
    protected function buildTree($groups): array
    {
        $tree = [];

        foreach ($groups as $group) {
            $node = [
                'id' => $group->id,
                'text' => $group->name . ' (' . $group->code . ')',
                'nature' => $group->nature,
                'is_system' => $group->is_system,
                'accounts_count' => $group->accounts->count(),
                'children' => [],
            ];

            if ($group->children->isNotEmpty()) {
                $node['children'] = $this->buildTree($group->children);
            }

            $tree[] = $node;
        }

        return $tree;
    }
}
