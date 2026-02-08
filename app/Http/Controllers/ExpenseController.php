<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
        $this->middleware('permission:expense-list')->only(['index']);
        $this->middleware('permission:expense-create')->only(['create', 'store']);
        $this->middleware('permission:expense-edit')->only(['edit', 'update']);
        $this->middleware('permission:expense-delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Expense::with(['category', 'expenseAccount', 'paymentAccount'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }
        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        $expenses = $query->paginate(25);
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $expenseGroups = AccountGroup::where('nature', 'expenses')
            ->orderBy('display_order')
            ->get();

        return view('expenses.index', compact('expenses', 'categories', 'expenseGroups'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentAccounts = Account::whereIn('account_type', ['cash', 'bank'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $expenseGroups = AccountGroup::where('nature', 'expenses')
            ->orderBy('display_order')
            ->get();

        return view('expenses.create', compact('categories', 'paymentAccounts', 'expenseGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {
            $category = ExpenseCategory::findOrFail($validated['expense_category_id']);
            $expenseAccount = $this->ensureCategoryAccount($category);

            $expense = Expense::create([
                'expense_category_id' => $category->id,
                'expense_account_id' => $expenseAccount->id,
                'payment_account_id' => $validated['payment_account_id'],
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->glService->replaceEntries('expense', $expense->id, $expense->expense_date, [
                [
                    'account_id' => $expenseAccount->id,
                    'debit_amount' => $expense->amount,
                    'credit_amount' => 0,
                    'reference' => $expense->reference,
                    'description' => $expense->description ?: 'Expense',
                ],
                [
                    'account_id' => $expense->payment_account_id,
                    'debit_amount' => 0,
                    'credit_amount' => $expense->amount,
                    'reference' => $expense->reference,
                    'description' => 'Expense payment',
                ],
            ]);

            return redirect()->route('expenses.index')
                ->with('success', 'Expense recorded successfully.');
        });
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentAccounts = Account::whereIn('account_type', ['cash', 'bank'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $expenseGroups = AccountGroup::where('nature', 'expenses')
            ->orderBy('display_order')
            ->get();

        return view('expenses.edit', compact('expense', 'categories', 'paymentAccounts', 'expenseGroups'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated, $expense) {
            $category = ExpenseCategory::findOrFail($validated['expense_category_id']);
            $expenseAccount = $this->ensureCategoryAccount($category);

            $expense->update([
                'expense_category_id' => $category->id,
                'expense_account_id' => $expenseAccount->id,
                'payment_account_id' => $validated['payment_account_id'],
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            $this->glService->replaceEntries('expense', $expense->id, $expense->expense_date, [
                [
                    'account_id' => $expenseAccount->id,
                    'debit_amount' => $expense->amount,
                    'credit_amount' => 0,
                    'reference' => $expense->reference,
                    'description' => $expense->description ?: 'Expense',
                ],
                [
                    'account_id' => $expense->payment_account_id,
                    'debit_amount' => 0,
                    'credit_amount' => $expense->amount,
                    'reference' => $expense->reference,
                    'description' => 'Expense payment',
                ],
            ]);

            return redirect()->route('expenses.index')
                ->with('success', 'Expense updated successfully.');
        });
    }

    public function destroy(Expense $expense)
    {
        $this->glService->removeEntries('expense', $expense->id);
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    protected function ensureCategoryAccount(ExpenseCategory $category): Account
    {
        if ($category->account) {
            return $category->account;
        }

        $groupId = $category->account_group_id;
        if (!$groupId) {
            $groupId = \App\Models\Accounting\AccountGroup::where('code', 'INDIRECT-EXPENSES')->value('id')
                ?? \App\Models\Accounting\AccountGroup::where('nature', 'expenses')->value('id');
        }

        if (!$groupId) {
            throw new \Exception('Expense account group not found. Please seed chart of accounts.');
        }

        $accountCode = 'EXP-' . str_pad($category->id, 5, '0', STR_PAD_LEFT);

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
            'account_group_id' => $groupId,
            'code' => $category->code ?: $accountCode,
        ]);

        return $account;
    }
}
