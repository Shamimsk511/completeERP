<?php

namespace App\Services;

use App\Models\Payee;
use App\Models\PayeeInstallment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayeeLoanService
{
    protected PayeeAccountService $accountService;
    protected \App\Services\Accounting\GeneralLedgerService $glService;

    public function __construct(PayeeAccountService $accountService, \App\Services\Accounting\GeneralLedgerService $glService)
    {
        $this->accountService = $accountService;
        $this->glService = $glService;
    }

    public function calculateCcInterest(Payee $payee, Carbon $asOf): array
    {
        $principal = (float) ($payee->principal_balance ?? 0);
        $rate = (float) ($payee->interest_rate ?? 0);
        $startDate = $payee->interest_last_accrual_date ?? $payee->loan_start_date;

        if ($principal <= 0 || $rate <= 0 || !$startDate) {
            return [
                'days' => 0,
                'daily_rate' => 0,
                'amount' => 0,
            ];
        }

        $startDate = Carbon::parse($startDate);
        if ($asOf->lessThanOrEqualTo($startDate)) {
            return [
                'days' => 0,
                'daily_rate' => 0,
                'amount' => 0,
            ];
        }

        $days = $startDate->diffInDays($asOf);
        $dailyRate = ($principal * ($rate / 100)) / 12 / 30;
        $amount = round($dailyRate * $days, 2);

        return [
            'days' => $days,
            'daily_rate' => round($dailyRate, 2),
            'amount' => $amount,
        ];
    }

    public function getCcInterestPreview(Payee $payee, Carbon $asOf): array
    {
        $pending = $this->calculateCcInterest($payee, $asOf);

        $principal = (float) ($payee->principal_balance ?? 0);
        $rate = (float) ($payee->interest_rate ?? 0);
        $startDate = $payee->loan_start_date;

        $lastPayment = $payee->transactions()
            ->where('transaction_type', 'cash_in')
            ->where(function ($query) {
                $query->where('interest_amount', '>', 0)
                    ->orWhere('category', 'interest_payment');
            })
            ->orderBy('transaction_date', 'desc')
            ->value('transaction_date');

        if ($lastPayment) {
            $startDate = $lastPayment;
        }

        if ($principal <= 0 || $rate <= 0 || !$startDate) {
            return [
                'days' => 0,
                'daily_rate' => 0,
                'amount' => $pending['amount'] ?? 0,
            ];
        }

        $startDate = Carbon::parse($startDate);
        if ($asOf->lessThanOrEqualTo($startDate)) {
            return [
                'days' => 0,
                'daily_rate' => 0,
                'amount' => $pending['amount'] ?? 0,
            ];
        }

        $days = $startDate->diffInDays($asOf);
        $dailyRate = ($principal * ($rate / 100)) / 12 / 30;

        return [
            'days' => $days,
            'daily_rate' => round($dailyRate, 2),
            'amount' => $pending['amount'] ?? 0,
        ];
    }

    public function accrueCcInterest(Payee $payee, Carbon $asOf, ?int $userId = null): bool
    {
        $calculation = $this->calculateCcInterest($payee, $asOf);
        if ($calculation['amount'] <= 0) {
            return false;
        }

        $payeeAccount = $this->accountService->ensureAccountForPayee($payee);
        $interestAccount = $this->accountService->ensureInterestExpenseAccount();

        if (!$payeeAccount || !$interestAccount) {
            return false;
        }

        return DB::transaction(function () use ($payee, $calculation, $asOf, $payeeAccount, $interestAccount, $userId) {
            $reference = 'CC-INT-' . $payee->id . '-' . $asOf->format('Ymd');
            $entryDate = $asOf->toDateString();

            $entries = [
                [
                    'account_id' => $interestAccount->id,
                    'debit_amount' => $calculation['amount'],
                    'credit_amount' => 0,
                    'reference' => $reference,
                    'description' => 'Interest expense',
                    'created_by' => $userId,
                ],
                [
                    'account_id' => $payeeAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $calculation['amount'],
                    'reference' => $reference,
                    'description' => 'Interest accrued',
                    'created_by' => $userId,
                ],
            ];

            $this->glService->appendEntries('payee_interest', $payee->id, $entryDate, $entries);

            $payee->interest_accrued = ($payee->interest_accrued ?? 0) + $calculation['amount'];
            $payee->interest_last_accrual_date = $asOf->toDateString();
            $payee->current_balance = ($payee->principal_balance ?? 0) + ($payee->interest_accrued ?? 0);
            $payee->save();

            return true;
        });
    }

    public function buildSmeSchedule(Payee $payee): void
    {
        if (!$payee->loan_start_date || !$payee->loan_term_months || $payee->principal_amount <= 0) {
            return;
        }

        $principal = (float) $payee->principal_amount;
        $term = (int) $payee->loan_term_months;
        $rate = (float) ($payee->interest_rate ?? 0);
        $monthlyRate = $rate > 0 ? ($rate / 100 / 12) : 0;

        if ($rate > 0) {
            $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $term)) / (pow(1 + $monthlyRate, $term) - 1);
        } else {
            $emi = $term > 0 ? ($principal / $term) : 0;
        }
        $emi = round($emi, 2);

        $payee->installment_amount = $emi;
        $payee->principal_balance = $payee->principal_balance > 0 ? $payee->principal_balance : $principal;
        $payee->save();

        $payee->installments()->delete();

        $balance = $principal;
        $startDate = Carbon::parse($payee->loan_start_date);

        for ($i = 1; $i <= $term; $i++) {
            $interest = $monthlyRate > 0 ? round($balance * $monthlyRate, 2) : 0;
            $principalPart = round($emi - $interest, 2);
            if ($i === $term) {
                $principalPart = round($balance, 2);
                $emi = round($principalPart + $interest, 2);
            }
            $balance = max(0, $balance - $principalPart);

            PayeeInstallment::create([
                'payee_id' => $payee->id,
                'installment_number' => $i,
                'due_date' => $startDate->copy()->addMonthsNoOverflow($i),
                'principal_due' => $principalPart,
                'interest_due' => $interest,
                'total_due' => $emi,
                'status' => 'pending',
            ]);
        }
    }

    public function getDailyKistiSummary(Payee $payee, Carbon $asOf): array
    {
        $startDate = $payee->daily_kisti_start_date ?? $payee->loan_start_date;
        if (!$startDate || $payee->daily_kisti_amount <= 0) {
            return [
                'total_days' => 0,
                'paid_days' => 0,
                'skipped_days' => 0,
                'pending_days' => 0,
                'pending_amount' => 0,
            ];
        }

        $startDate = Carbon::parse($startDate);
        if ($asOf->lessThanOrEqualTo($startDate)) {
            return [
                'total_days' => 0,
                'paid_days' => 0,
                'skipped_days' => 0,
                'pending_days' => 0,
                'pending_amount' => 0,
            ];
        }

        $totalDays = $startDate->diffInDays($asOf);
        $skippedDays = $payee->kistiSkips()->whereBetween('skip_date', [$startDate->toDateString(), $asOf->toDateString()])->count();
        $paidDays = (int) $payee->transactions()->whereNotNull('kisti_days')->sum('kisti_days');

        $pendingDays = max(0, $totalDays - $skippedDays - $paidDays);
        $pendingAmount = $pendingDays * (float) $payee->daily_kisti_amount;

        return [
            'total_days' => $totalDays,
            'paid_days' => $paidDays,
            'skipped_days' => $skippedDays,
            'pending_days' => $pendingDays,
            'pending_amount' => round($pendingAmount, 2),
        ];
    }
}
