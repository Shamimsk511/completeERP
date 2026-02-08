<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:expense-list')->only(['index']);
        $this->middleware('permission:expense-create')->only(['create', 'store']);
        $this->middleware('permission:expense-edit')->only(['edit', 'update']);
        $this->middleware('permission:expense-delete')->only(['destroy']);
    }

    public function index()
    {
        $categories = ExpenseCategory::with(['account', 'accountGroup'])
            ->orderBy('name')
            ->paginate(25);

        return view('expenses.categories.index', compact('categories'));
    }

    public function create()
    {
        $expenseGroups = AccountGroup::where('nature', 'expenses')
            ->orderBy('display_order')
            ->get();

        return view('expenses.categories.create', compact('expenseGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'account_group_id' => 'nullable|exists:account_groups,id',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {
            $groupId = $this->resolveExpenseGroupId($validated['account_group_id'] ?? null);

            $category = ExpenseCategory::create([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'account_group_id' => $groupId,
                'is_active' => request()->boolean('is_active'),
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $accountCode = $this->resolveAccountCode($category, $validated['code'] ?? null);

            $account = Account::create([
                'name' => $category->name,
                'code' => $accountCode,
                'account_group_id' => $groupId,
                'account_type' => 'expense',
                'opening_balance' => 0,
                'opening_balance_type' => 'debit',
                'current_balance' => 0,
                'current_balance_type' => 'debit',
                'is_active' => true,
                'is_system' => false,
                'notes' => "Auto-created for expense category: {$category->name}",
            ]);

            $category->update([
                'account_id' => $account->id,
                'code' => $category->code ?: $accountCode,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'id' => $category->id,
                    'name' => $category->name,
                    'code' => $category->code,
                ]);
            }

            return redirect()->route('expenses.categories.index')
                ->with('success', 'Expense category created successfully.');
        });
    }

    public function edit(ExpenseCategory $category)
    {
        $expenseGroups = AccountGroup::where('nature', 'expenses')
            ->orderBy('display_order')
            ->get();

        return view('expenses.categories.edit', compact('category', 'expenseGroups'));
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'account_group_id' => 'nullable|exists:account_groups,id',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated, $category) {
            $groupId = $this->resolveExpenseGroupId($validated['account_group_id'] ?? $category->account_group_id);

            $category->update([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? $category->code,
                'account_group_id' => $groupId,
                'is_active' => request()->boolean('is_active'),
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($category->account) {
                $category->account->update([
                    'name' => $category->name,
                    'account_group_id' => $groupId,
                ]);
            } else {
                $accountCode = $this->resolveAccountCode($category, $validated['code'] ?? null);
                $account = Account::create([
                    'name' => $category->name,
                    'code' => $accountCode,
                    'account_group_id' => $groupId,
                    'account_type' => 'expense',
                    'opening_balance' => 0,
                    'opening_balance_type' => 'debit',
                    'current_balance' => 0,
                    'current_balance_type' => 'debit',
                    'is_active' => true,
                    'is_system' => false,
                    'notes' => "Auto-created for expense category: {$category->name}",
                ]);
                $category->update([
                    'account_id' => $account->id,
                    'code' => $category->code ?: $accountCode,
                ]);
            }

            return redirect()->route('expenses.categories.index')
                ->with('success', 'Expense category updated successfully.');
        });
    }

    public function destroy(ExpenseCategory $category)
    {
        if ($category->expenses()->exists()) {
            return back()->with('error', 'Cannot delete a category with expenses.');
        }

        $category->delete();

        return redirect()->route('expenses.categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }

    protected function resolveExpenseGroupId(?int $preferredId): int
    {
        if ($preferredId) {
            return $preferredId;
        }

        $fallback = AccountGroup::where('code', 'INDIRECT-EXPENSES')->value('id');
        if ($fallback) {
            return $fallback;
        }

        $first = AccountGroup::where('nature', 'expenses')->value('id');
        if (!$first) {
            throw new \Exception('Expense account group not found. Please seed chart of accounts.');
        }

        return $first;
    }

    protected function resolveAccountCode(ExpenseCategory $category, ?string $preferredCode): string
    {
        if ($preferredCode && !Account::where('code', $preferredCode)->exists()) {
            return $preferredCode;
        }

        return 'EXP-' . str_pad($category->id, 5, '0', STR_PAD_LEFT);
    }
}
