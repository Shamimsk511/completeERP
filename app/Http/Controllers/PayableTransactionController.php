<?php

namespace App\Http\Controllers;

use App\Models\Payee;
use App\Models\PayableTransaction;
use App\Models\PayeeInstallment;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\GeneralLedgerService;
use App\Services\PayeeAccountService;
use App\Services\PayeeLoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;

class PayableTransactionController extends Controller
{
    protected AutoPostingService $autoPostingService;
    protected GeneralLedgerService $glService;
    protected PayeeAccountService $payeeAccountService;
    protected PayeeLoanService $payeeLoanService;

    public function __construct(
        AutoPostingService $autoPostingService,
        GeneralLedgerService $glService,
        PayeeAccountService $payeeAccountService,
        PayeeLoanService $payeeLoanService
    )
    {
        $this->autoPostingService = $autoPostingService;
        $this->glService = $glService;
        $this->payeeAccountService = $payeeAccountService;
        $this->payeeLoanService = $payeeLoanService;
    $this->middleware('permission:payable-transaction-list|payable-transaction-create|payable-transaction-edit|payable-transaction-delete', ['only' => ['index']]);
    $this->middleware('permission:payable-transaction-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:payable-transaction-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:payable-transaction-delete', ['only' => ['destroy']]);
}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PayableTransaction::with('payee')
                ->select('payable_transactions.*');

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereDate('transaction_date', '>=', $request->date_from)
                    ->whereDate('transaction_date', '<=', $request->date_to);
            }

            if ($request->filled('payee_id')) {
                $query->where('payee_id', $request->payee_id);
            }

            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            
            return DataTables::of($query)
                ->addColumn('payee_name', function ($transaction) {
                    if (!$transaction->payee) {
                        return 'N/A';
                    }
                    $payeeLink = route('payees.show', $transaction->payee_id);
                    return '<a href="' . $payeeLink . '">' . e($transaction->payee->name) . '</a>';
                })
                ->editColumn('transaction_date', function ($transaction) {
                    return $transaction->transaction_date->format('Y-m-d');
                })
                ->editColumn('amount', function ($transaction) {
                    return number_format($transaction->amount, 2);
                })
                ->editColumn('transaction_type', function ($transaction) {
                    return $transaction->transaction_type == 'cash_in'
                        ? '<span class="badge badge-success">Payment</span>'
                        : '<span class="badge badge-danger">Received</span>';
                })
                ->addColumn('action', function ($transaction) {
                    return '
                        <div class="btn-group">
                            <a href="' . route('payable-transactions.edit', $transaction->id) . '" class="btn modern-btn modern-btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn modern-btn modern-btn-danger btn-sm delete-transaction" data-id="' . $transaction->id . '">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['transaction_type', 'action', 'payee_name'])
                ->make(true);
        }

        // Summary for dashboard
        $totalCashIn = PayableTransaction::where('transaction_type', 'cash_in')->sum('amount');
        $totalCashOut = PayableTransaction::where('transaction_type', 'cash_out')->sum('amount');
        $netPayable = $totalCashOut - $totalCashIn;
        
        $payees = Payee::orderBy('name')->get(['id', 'name', 'phone']);

        return view('payables.transactions.index', compact('totalCashIn', 'totalCashOut', 'netPayable', 'payees'));
    }

    public function create()
    {
        $cashInSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_in_total'))
            ->where('transaction_type', 'cash_in')
            ->groupBy('payee_id');
        $cashOutSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_out_total'))
            ->where('transaction_type', 'cash_out')
            ->groupBy('payee_id');

        $ledgerBalanceExpression = "
            CASE
                WHEN COALESCE(payees.opening_balance, 0) = 0
                    AND COALESCE(payee_cash_in.cash_in_total, 0) = 0
                    AND COALESCE(payee_cash_out.cash_out_total, 0) = 0
                    AND COALESCE(payees.current_balance, 0) <> 0
                THEN COALESCE(payees.current_balance, 0)
                ELSE
                    COALESCE(payees.opening_balance, 0)
                    + COALESCE(payee_cash_out.cash_out_total, 0)
                    - COALESCE(payee_cash_in.cash_in_total, 0)
            END
        ";

        $payees = Payee::query()
            ->leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_in.payee_id');
            })
            ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_out.payee_id');
            })
            ->select('payees.*', DB::raw($ledgerBalanceExpression . ' as ledger_balance'))
            ->orderBy('payees.name')
            ->get();
        $cashBankAccounts = AutoPostingService::getCashBankAccounts();
        $selectedPayee = null;
        $interestPreview = null;
        $installment = null;
        $kistiSummary = null;

        if (request()->filled('payee_id')) {
            $selectedPayee = Payee::find(request()->get('payee_id'));
            if ($selectedPayee?->isCcLoan()) {
                $interestPreview = $this->payeeLoanService->getCcInterestPreview($selectedPayee, Carbon::today());
            }
            if ($selectedPayee?->isDailyKisti()) {
                $kistiSummary = $this->payeeLoanService->getDailyKistiSummary($selectedPayee, Carbon::today());
            }
        }

        if (request()->filled('installment_id')) {
            $installment = PayeeInstallment::find(request()->get('installment_id'));
        }

        return view('payables.transactions.create', compact(
            'payees',
            'cashBankAccounts',
            'selectedPayee',
            'interestPreview',
            'installment',
            'kistiSummary'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payee_id' => 'required|exists:payees,id',
            'transaction_type' => 'required|in:cash_in,cash_out',
            'payment_method' => 'nullable|string|max:50',
            'account_id' => 'nullable|exists:accounts,id',
            'reference_no' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'principal_amount' => 'nullable|numeric|min:0',
            'interest_amount' => 'nullable|numeric|min:0',
            'kisti_days' => 'nullable|integer|min:0',
            'installment_id' => 'nullable|exists:payee_installments,id',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $payee = Payee::findOrFail($validated['payee_id']);
            $this->payeeAccountService->ensureAccountForPayee($payee);

            $transactionDate = Carbon::parse($validated['transaction_date']);
            if ($payee->isCcLoan()) {
                $this->payeeLoanService->accrueCcInterest($payee, $transactionDate, auth()->id());
                $payee->refresh();
            }

            $principalAmount = (float) ($validated['principal_amount'] ?? 0);
            $interestAmount = (float) ($validated['interest_amount'] ?? 0);
            $validated['kisti_days'] = $validated['kisti_days'] ?? 0;

            if (!empty($validated['installment_id'])) {
                $installment = PayeeInstallment::findOrFail($validated['installment_id']);
                $principalAmount = (float) $installment->principal_due;
                $interestAmount = (float) $installment->interest_due;
                $validated['amount'] = (float) $installment->total_due;
            }

            if ($payee->isDailyKisti() && $payee->daily_kisti_amount > 0) {
                if (empty($validated['kisti_days'])) {
                    $validated['kisti_days'] = (int) floor($validated['amount'] / $payee->daily_kisti_amount);
                }
                $principalAmount = (float) $validated['amount'];
                $interestAmount = 0;
            }

            if ($payee->isLoanCategory()) {
                if ($payee->isCcLoan()) {
                    $interestAmount = min($interestAmount, (float) ($payee->interest_accrued ?? 0));
                }
                if ($principalAmount <= 0 && $interestAmount <= 0) {
                    $principalAmount = (float) $validated['amount'];
                }
                $validated['principal_amount'] = $principalAmount;
                $validated['interest_amount'] = $interestAmount;
                $validated['amount'] = (float) $principalAmount + (float) $interestAmount;
            } else {
                $validated['principal_amount'] = $principalAmount;
                $validated['interest_amount'] = $interestAmount;
            }

            // Create the transaction
            $transaction = PayableTransaction::create($validated);

            // Update the payee's current balance
            if ($payee->isLoanCategory()) {
                if ($validated['transaction_type'] == 'cash_in') {
                    $payee->principal_balance -= $principalAmount;
                    if ($payee->isCcLoan()) {
                        $payee->interest_accrued = max(0, ($payee->interest_accrued ?? 0) - $interestAmount);
                    }
                } else {
                    $payee->principal_balance += $principalAmount;
                }
                $payee->principal_balance = max(0, $payee->principal_balance ?? 0);
                $payee->current_balance = ($payee->principal_balance ?? 0) + ($payee->isCcLoan() ? ($payee->interest_accrued ?? 0) : 0);
            } else {
                if ($validated['transaction_type'] == 'cash_in') {
                    // Cash in reduces what we owe
                    $payee->current_balance -= $validated['amount'];
                } else {
                    // Cash out increases what we owe
                    $payee->current_balance += $validated['amount'];
                }
            }

            $payee->save();

            if (!empty($validated['installment_id']) && $validated['transaction_type'] === 'cash_in') {
                $installment = PayeeInstallment::find($validated['installment_id']);
                if ($installment) {
                    $installment->status = 'paid';
                    $installment->paid_at = $validated['transaction_date'];
                    $installment->save();
                }
            }

            // Create accounting entries
            try {
                $this->autoPostingService->postPayableTransaction($transaction);
            } catch (\Exception $e) {
                Log::warning("Failed to auto-post payable transaction {$transaction->id}: " . $e->getMessage());
                // Don't fail the transaction, just log the warning
            }

            DB::commit();

            return redirect()->route('payable-transactions.index')
                ->with('success', 'Transaction recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error recording transaction: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(PayableTransaction $payableTransaction)
    {
        $cashInSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_in_total'))
            ->where('transaction_type', 'cash_in')
            ->groupBy('payee_id');
        $cashOutSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_out_total'))
            ->where('transaction_type', 'cash_out')
            ->groupBy('payee_id');

        $ledgerBalanceExpression = "
            CASE
                WHEN COALESCE(payees.opening_balance, 0) = 0
                    AND COALESCE(payee_cash_in.cash_in_total, 0) = 0
                    AND COALESCE(payee_cash_out.cash_out_total, 0) = 0
                    AND COALESCE(payees.current_balance, 0) <> 0
                THEN COALESCE(payees.current_balance, 0)
                ELSE
                    COALESCE(payees.opening_balance, 0)
                    + COALESCE(payee_cash_out.cash_out_total, 0)
                    - COALESCE(payee_cash_in.cash_in_total, 0)
            END
        ";

        $payees = Payee::query()
            ->leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_in.payee_id');
            })
            ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_out.payee_id');
            })
            ->select('payees.*', DB::raw($ledgerBalanceExpression . ' as ledger_balance'))
            ->orderBy('payees.name')
            ->get();
        $cashBankAccounts = AutoPostingService::getCashBankAccounts();
        return view('payables.transactions.edit', [
            'transaction' => $payableTransaction,
            'payees' => $payees,
            'cashBankAccounts' => $cashBankAccounts
        ]);
    }

    public function update(Request $request, PayableTransaction $payableTransaction)
    {
        $validated = $request->validate([
            'payee_id' => 'required|exists:payees,id',
            'transaction_type' => 'required|in:cash_in,cash_out',
            'payment_method' => 'nullable|string|max:50',
            'account_id' => 'nullable|exists:accounts,id',
            'reference_no' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'principal_amount' => 'nullable|numeric|min:0',
            'interest_amount' => 'nullable|numeric|min:0',
            'kisti_days' => 'nullable|integer|min:0',
            'installment_id' => 'nullable|exists:payee_installments,id',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            // Get the original transaction data
            $originalPayeeId = $payableTransaction->payee_id;
            $originalType = $payableTransaction->transaction_type;
            $originalAmount = $payableTransaction->amount;
            
            $payee = Payee::findOrFail($validated['payee_id']);
            $this->payeeAccountService->ensureAccountForPayee($payee);

            $transactionDate = Carbon::parse($validated['transaction_date']);
            if ($payee->isCcLoan()) {
                $this->payeeLoanService->accrueCcInterest($payee, $transactionDate, auth()->id());
                $payee->refresh();
            }

            $principalAmount = (float) ($validated['principal_amount'] ?? 0);
            $interestAmount = (float) ($validated['interest_amount'] ?? 0);
            $validated['kisti_days'] = $validated['kisti_days'] ?? 0;

            if (!empty($validated['installment_id'])) {
                $installment = PayeeInstallment::findOrFail($validated['installment_id']);
                $principalAmount = (float) $installment->principal_due;
                $interestAmount = (float) $installment->interest_due;
                $validated['amount'] = (float) $installment->total_due;
            }

            if ($payee->isDailyKisti() && $payee->daily_kisti_amount > 0) {
                if (empty($validated['kisti_days'])) {
                    $validated['kisti_days'] = (int) floor($validated['amount'] / $payee->daily_kisti_amount);
                }
                $principalAmount = (float) $validated['amount'];
                $interestAmount = 0;
            }

            if ($payee->isLoanCategory()) {
                if ($payee->isCcLoan()) {
                    $interestAmount = min($interestAmount, (float) ($payee->interest_accrued ?? 0));
                }
                if ($principalAmount <= 0 && $interestAmount <= 0) {
                    $principalAmount = (float) $validated['amount'];
                }
                $validated['principal_amount'] = $principalAmount;
                $validated['interest_amount'] = $interestAmount;
                $validated['amount'] = (float) $principalAmount + (float) $interestAmount;
            } else {
                $validated['principal_amount'] = $principalAmount;
                $validated['interest_amount'] = $interestAmount;
            }

            // Reverse the effect of the original transaction
            $originalPayee = Payee::findOrFail($originalPayeeId);
            if ($originalPayee->isLoanCategory()) {
                $originalPrincipal = $payableTransaction->principal_amount ?? 0;
                $originalInterest = $payableTransaction->interest_amount ?? 0;
                if ($originalType == 'cash_in') {
                    $originalPayee->principal_balance += $originalPrincipal;
                    if ($originalPayee->isCcLoan()) {
                        $originalPayee->interest_accrued += $originalInterest;
                    }
                } else {
                    $originalPayee->principal_balance -= $originalPrincipal;
                }
                $originalPayee->current_balance = ($originalPayee->principal_balance ?? 0) + ($originalPayee->isCcLoan() ? ($originalPayee->interest_accrued ?? 0) : 0);
                $originalPayee->save();
            } else {
                if ($originalType == 'cash_in') {
                    $originalPayee->current_balance += $originalAmount;
                } else {
                    $originalPayee->current_balance -= $originalAmount;
                }
                $originalPayee->save();
            }

            if ($payableTransaction->installment_id && $originalType === 'cash_in') {
                $originalInstallment = PayeeInstallment::find($payableTransaction->installment_id);
                if ($originalInstallment) {
                    $originalInstallment->status = 'pending';
                    $originalInstallment->paid_at = null;
                    $originalInstallment->save();
                }
            }

            // Apply the new transaction
            $payableTransaction->update($validated);
            
            // Update the payee's current balance with the new transaction
            $newPayee = $payee;
            if ($newPayee->isLoanCategory()) {
                if ($validated['transaction_type'] == 'cash_in') {
                    $newPayee->principal_balance -= $principalAmount;
                    if ($newPayee->isCcLoan()) {
                        $newPayee->interest_accrued = max(0, ($newPayee->interest_accrued ?? 0) - $interestAmount);
                    }
                } else {
                    $newPayee->principal_balance += $principalAmount;
                }
                $newPayee->principal_balance = max(0, $newPayee->principal_balance ?? 0);
                $newPayee->current_balance = ($newPayee->principal_balance ?? 0) + ($newPayee->isCcLoan() ? ($newPayee->interest_accrued ?? 0) : 0);
                $newPayee->save();
            } else {
                if ($validated['transaction_type'] == 'cash_in') {
                    $newPayee->current_balance -= $validated['amount'];
                } else {
                    $newPayee->current_balance += $validated['amount'];
                }
                $newPayee->save();
            }

            if (!empty($validated['installment_id']) && $validated['transaction_type'] === 'cash_in') {
                $installment = PayeeInstallment::find($validated['installment_id']);
                if ($installment) {
                    $installment->status = 'paid';
                    $installment->paid_at = $validated['transaction_date'];
                    $installment->save();
                }
            }

            try {
                $this->autoPostingService->postPayableTransaction($payableTransaction);
            } catch (\Exception $e) {
                Log::warning("Failed to update payable ledger entries {$payableTransaction->id}: " . $e->getMessage());
            }
            
            DB::commit();
            
            return redirect()->route('payable-transactions.index')
                ->with('success', 'Transaction updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating transaction: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(PayableTransaction $payableTransaction)
    {
        DB::beginTransaction();
        try {
            // Reverse the effect of this transaction on the payee's balance
            $payee = $payableTransaction->payee;
            
            if ($payee && $payee->isLoanCategory()) {
                $principal = $payableTransaction->principal_amount ?? 0;
                $interest = $payableTransaction->interest_amount ?? 0;
                if ($payableTransaction->transaction_type == 'cash_in') {
                    $payee->principal_balance += $principal;
                    if ($payee->isCcLoan()) {
                        $payee->interest_accrued += $interest;
                    }
                } else {
                    $payee->principal_balance -= $principal;
                }
                $payee->current_balance = ($payee->principal_balance ?? 0) + ($payee->isCcLoan() ? ($payee->interest_accrued ?? 0) : 0);
            } else {
                if ($payableTransaction->transaction_type == 'cash_in') {
                    $payee->current_balance += $payableTransaction->amount;
                } else {
                    $payee->current_balance -= $payableTransaction->amount;
                }
            }
            
            if ($payee) {
                $payee->save();
            }

            if ($payableTransaction->installment_id) {
                $installment = PayeeInstallment::find($payableTransaction->installment_id);
                if ($installment) {
                    $installment->status = 'pending';
                    $installment->paid_at = null;
                    $installment->save();
                }
            }
            
            $this->glService->removeEntries('payable_transaction', $payableTransaction->id);

            // Delete the transaction
            $payableTransaction->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting transaction: ' . $e->getMessage()
            ]);
        }
    }
}
