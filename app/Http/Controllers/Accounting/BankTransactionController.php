<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\BankTransaction;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankTransactionController extends Controller
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
        $this->middleware('permission:account-list')->only(['index']);
        $this->middleware('permission:account-create')->only(['create', 'store']);
        $this->middleware('permission:account-edit')->only(['edit', 'update']);
        $this->middleware('permission:account-delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = BankTransaction::with(['bankAccount', 'counterAccount'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->paginate(25)->withQueryString();

        $banks = Account::where('account_type', 'bank')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('accounting.bank-transactions.index', compact('transactions', 'banks'));
    }

    public function create(Request $request)
    {
        $banks = Account::where('account_type', 'bank')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cashAccounts = Account::where('account_type', 'cash')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $expenseAccounts = AutoPostingService::getExpenseAccounts();
        $incomeAccounts = AutoPostingService::getIncomeAccounts();

        $selectedBank = $request->get('bank_account_id');
        $selectedType = $request->get('type');

        return view('accounting.bank-transactions.create', compact(
            'banks',
            'cashAccounts',
            'expenseAccounts',
            'incomeAccounts',
            'selectedBank',
            'selectedType'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'bank_account_id' => 'required|exists:accounts,id',
            'transaction_type' => 'required|in:deposit,withdraw,adjustment',
            'direction' => 'nullable|in:in,out',
            'counter_account_id' => 'nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $bankAccount = Account::where('id', $validated['bank_account_id'])
            ->where('account_type', 'bank')
            ->firstOrFail();

        $transactionType = $validated['transaction_type'];
        $direction = $transactionType === 'adjustment'
            ? ($validated['direction'] ?? null)
            : ($transactionType === 'deposit' ? 'in' : 'out');

        if ($transactionType === 'adjustment' && !$direction) {
            return back()->withErrors(['direction' => 'Direction is required for adjustments.'])->withInput();
        }

        $counterAccountId = $validated['counter_account_id'] ?? null;
        if (!$counterAccountId) {
            return back()->withErrors(['counter_account_id' => 'Please select the counter account.'])->withInput();
        }

        $counterAccount = Account::findOrFail($counterAccountId);

        if ($counterAccount->id === $bankAccount->id) {
            return back()->withErrors(['counter_account_id' => 'Counter account cannot be the same as the bank account.'])->withInput();
        }

        if (in_array($transactionType, ['deposit', 'withdraw'], true)) {
            if (!in_array($counterAccount->account_type, ['cash', 'bank'], true)) {
                return back()->withErrors(['counter_account_id' => 'Please select a cash or bank account.'])->withInput();
            }
        }

        if ($transactionType === 'adjustment') {
            if (!in_array($counterAccount->account_type, ['expense', 'income'], true)) {
                return back()->withErrors(['counter_account_id' => 'Please select an expense or income account for adjustments.'])->withInput();
            }
        }

        return DB::transaction(function () use ($validated, $bankAccount, $counterAccount, $direction, $transactionType) {
            $transaction = BankTransaction::create([
                'bank_account_id' => $bankAccount->id,
                'counter_account_id' => $counterAccount->id,
                'transaction_date' => $validated['transaction_date'],
                'transaction_type' => $transactionType,
                'direction' => $direction,
                'amount' => $validated['amount'],
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $reference = $transaction->reference ?? ('BTRX-' . $transaction->id);

            $entries = $this->buildEntries($transactionType, $direction, $bankAccount, $counterAccount, (float) $transaction->amount, $reference, $transaction->description);

            $this->glService->replaceEntries('bank_transaction', $transaction->id, $transaction->transaction_date->toDateString(), $entries);

            return redirect()->route('accounting.bank-transactions.index')
                ->with('success', 'Bank transaction recorded successfully.');
        });
    }

    public function edit(BankTransaction $bankTransaction)
    {
        $banks = Account::where('account_type', 'bank')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cashAccounts = Account::where('account_type', 'cash')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $expenseAccounts = AutoPostingService::getExpenseAccounts();
        $incomeAccounts = AutoPostingService::getIncomeAccounts();

        return view('accounting.bank-transactions.edit', compact(
            'bankTransaction',
            'banks',
            'cashAccounts',
            'expenseAccounts',
            'incomeAccounts'
        ));
    }

    public function update(Request $request, BankTransaction $bankTransaction)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'bank_account_id' => 'required|exists:accounts,id',
            'transaction_type' => 'required|in:deposit,withdraw,adjustment',
            'direction' => 'nullable|in:in,out',
            'counter_account_id' => 'nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $bankAccount = Account::where('id', $validated['bank_account_id'])
            ->where('account_type', 'bank')
            ->firstOrFail();

        $transactionType = $validated['transaction_type'];
        $direction = $transactionType === 'adjustment'
            ? ($validated['direction'] ?? null)
            : ($transactionType === 'deposit' ? 'in' : 'out');

        if ($transactionType === 'adjustment' && !$direction) {
            return back()->withErrors(['direction' => 'Direction is required for adjustments.'])->withInput();
        }

        $counterAccountId = $validated['counter_account_id'] ?? null;
        if (!$counterAccountId) {
            return back()->withErrors(['counter_account_id' => 'Please select the counter account.'])->withInput();
        }

        $counterAccount = Account::findOrFail($counterAccountId);

        if ($counterAccount->id === $bankAccount->id) {
            return back()->withErrors(['counter_account_id' => 'Counter account cannot be the same as the bank account.'])->withInput();
        }

        if (in_array($transactionType, ['deposit', 'withdraw'], true)) {
            if (!in_array($counterAccount->account_type, ['cash', 'bank'], true)) {
                return back()->withErrors(['counter_account_id' => 'Please select a cash or bank account.'])->withInput();
            }
        }

        if ($transactionType === 'adjustment') {
            if (!in_array($counterAccount->account_type, ['expense', 'income'], true)) {
                return back()->withErrors(['counter_account_id' => 'Please select an expense or income account for adjustments.'])->withInput();
            }
        }

        return DB::transaction(function () use ($validated, $bankTransaction, $bankAccount, $counterAccount, $direction, $transactionType) {
            $bankTransaction->update([
                'bank_account_id' => $bankAccount->id,
                'counter_account_id' => $counterAccount->id,
                'transaction_date' => $validated['transaction_date'],
                'transaction_type' => $transactionType,
                'direction' => $direction,
                'amount' => $validated['amount'],
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            $reference = $bankTransaction->reference ?? ('BTRX-' . $bankTransaction->id);
            $entries = $this->buildEntries($transactionType, $direction, $bankAccount, $counterAccount, (float) $bankTransaction->amount, $reference, $bankTransaction->description);

            $this->glService->replaceEntries('bank_transaction', $bankTransaction->id, $bankTransaction->transaction_date->toDateString(), $entries);

            return redirect()->route('accounting.bank-transactions.index')
                ->with('success', 'Bank transaction updated successfully.');
        });
    }

    public function destroy(BankTransaction $bankTransaction)
    {
        $this->glService->removeEntries('bank_transaction', $bankTransaction->id);
        $bankTransaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank transaction deleted successfully.',
        ]);
    }

    protected function buildEntries(string $type, string $direction, Account $bankAccount, Account $counterAccount, float $amount, ?string $reference, ?string $description): array
    {
        $description = $description ?: match ($type) {
            'deposit' => "Deposit from {$counterAccount->name}",
            'withdraw' => "Withdrawal to {$counterAccount->name}",
            'adjustment' => $direction === 'in' ? 'Bank adjustment (income)' : 'Bank adjustment (expense)',
            default => 'Bank transaction',
        };

        if ($type === 'deposit') {
            return [
                [
                    'account_id' => $bankAccount->id,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'reference' => $reference,
                    'description' => $description,
                ],
                [
                    'account_id' => $counterAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'reference' => $reference,
                    'description' => $description,
                ],
            ];
        }

        if ($type === 'withdraw') {
            return [
                [
                    'account_id' => $counterAccount->id,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'reference' => $reference,
                    'description' => $description,
                ],
                [
                    'account_id' => $bankAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'reference' => $reference,
                    'description' => $description,
                ],
            ];
        }

        if ($direction === 'in') {
            return [
                [
                    'account_id' => $bankAccount->id,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'reference' => $reference,
                    'description' => $description,
                ],
                [
                    'account_id' => $counterAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'reference' => $reference,
                    'description' => $description,
                ],
            ];
        }

        return [
            [
                'account_id' => $counterAccount->id,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'reference' => $reference,
                'description' => $description,
            ],
            [
                'account_id' => $bankAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'reference' => $reference,
                'description' => $description,
            ],
        ];
    }
}
