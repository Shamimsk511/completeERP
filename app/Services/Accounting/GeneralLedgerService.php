<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\AccountEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneralLedgerService
{
    /**
     * Replace ledger entries for a source
     */
    public function replaceEntries(string $sourceType, int $sourceId, string $entryDate, array $entries): void
    {
        $accountIds = collect($entries)->pluck('account_id')->unique()->filter()->values();

        DB::transaction(function () use ($sourceType, $sourceId, $entryDate, $entries) {
            AccountEntry::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->delete();

            foreach ($entries as $entry) {
                AccountEntry::create([
                    'account_id' => $entry['account_id'],
                    'entry_date' => $entryDate,
                    'debit_amount' => $entry['debit_amount'] ?? 0,
                    'credit_amount' => $entry['credit_amount'] ?? 0,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'reference' => $entry['reference'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'created_by' => $entry['created_by'] ?? auth()->id(),
                ]);
            }
        });

        $this->updateBalancesForAccountIds($accountIds->all());
    }

    /**
     * Append ledger entries for a source without deleting existing entries
     */
    public function appendEntries(string $sourceType, ?int $sourceId, string $entryDate, array $entries): void
    {
        $accountIds = collect($entries)->pluck('account_id')->unique()->filter()->values();

        DB::transaction(function () use ($sourceType, $sourceId, $entryDate, $entries) {
            foreach ($entries as $entry) {
                AccountEntry::create([
                    'account_id' => $entry['account_id'],
                    'entry_date' => $entryDate,
                    'debit_amount' => $entry['debit_amount'] ?? 0,
                    'credit_amount' => $entry['credit_amount'] ?? 0,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'reference' => $entry['reference'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'created_by' => $entry['created_by'] ?? auth()->id(),
                ]);
            }
        });

        $this->updateBalancesForAccountIds($accountIds->all());
    }

    /**
     * Update account current balance
     */
    public function updateAccountBalance(Account $account): void
    {
        $balance = $this->calculateAccountBalance($account);

        $account->update([
            'current_balance' => $balance['balance'],
            'current_balance_type' => $balance['type'],
        ]);
    }

    /**
     * Calculate account balance from all posted ledger entries
     */
    public function calculateAccountBalance(Account $account): array
    {
        $debits = AccountEntry::where('account_id', $account->id)->sum('debit_amount');
        $credits = AccountEntry::where('account_id', $account->id)->sum('credit_amount');

        $openingDebit = $account->opening_balance_type === 'debit' ? $account->opening_balance : 0;
        $openingCredit = $account->opening_balance_type === 'credit' ? $account->opening_balance : 0;

        $totalDebits = $openingDebit + $debits;
        $totalCredits = $openingCredit + $credits;

        return [
            'debit' => $totalDebits,
            'credit' => $totalCredits,
            'balance' => abs($totalDebits - $totalCredits),
            'type' => $totalDebits >= $totalCredits ? 'debit' : 'credit',
        ];
    }

    /**
     * Get account ledger for date range
     */
    public function getAccountLedger(Account $account, $fromDate, $toDate): array
    {
        $openingBalance = $this->getOpeningBalance($account, $fromDate);

        $entries = AccountEntry::where('account_id', $account->id)
            ->whereBetween('entry_date', [$fromDate, $toDate])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $runningBalance = $openingBalance['balance'];
        $balanceType = $openingBalance['type'];

        $ledgerEntries = [];
        foreach ($entries as $entry) {
            // Calculate running balance based on account nature
            $accountNature = $account->accountGroup->nature;
            $isNaturalDebit = in_array($accountNature, ['assets', 'expenses']);

            if ($entry->debit_amount > 0) {
                if ($isNaturalDebit) {
                    // Debit increases balance for assets/expenses
                    if ($balanceType === 'debit') {
                        $runningBalance += $entry->debit_amount;
                    } else {
                        $runningBalance -= $entry->debit_amount;
                        if ($runningBalance < 0) {
                            $runningBalance = abs($runningBalance);
                            $balanceType = 'debit';
                        }
                    }
                } else {
                    // Debit decreases balance for liabilities/income/capital
                    if ($balanceType === 'credit') {
                        $runningBalance -= $entry->debit_amount;
                        if ($runningBalance < 0) {
                            $runningBalance = abs($runningBalance);
                            $balanceType = 'debit';
                        }
                    } else {
                        $runningBalance += $entry->debit_amount;
                    }
                }
            } else {
                if ($isNaturalDebit) {
                    // Credit decreases balance for assets/expenses
                    if ($balanceType === 'debit') {
                        $runningBalance -= $entry->credit_amount;
                        if ($runningBalance < 0) {
                            $runningBalance = abs($runningBalance);
                            $balanceType = 'credit';
                        }
                    } else {
                        $runningBalance += $entry->credit_amount;
                    }
                } else {
                    // Credit increases balance for liabilities/income/capital
                    if ($balanceType === 'credit') {
                        $runningBalance += $entry->credit_amount;
                    } else {
                        $runningBalance -= $entry->credit_amount;
                        if ($runningBalance < 0) {
                            $runningBalance = abs($runningBalance);
                            $balanceType = 'credit';
                        }
                    }
                }
            }

            $ledgerEntries[] = [
                'date' => $entry->entry_date,
                'reference' => $entry->reference,
                'source_type' => $entry->source_type,
                'particulars' => $entry->description,
                'debit' => $entry->debit_amount,
                'credit' => $entry->credit_amount,
                'running_balance' => $runningBalance,
                'balance_type' => $balanceType,
            ];
        }

        return [
            'account' => $account,
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'opening_balance' => $openingBalance,
            'entries' => $ledgerEntries,
            'closing_balance' => [
                'balance' => $runningBalance,
                'type' => $balanceType,
            ],
            'totals' => [
                'debit' => collect($ledgerEntries)->sum('debit'),
                'credit' => collect($ledgerEntries)->sum('credit'),
            ],
        ];
    }

    /**
     * Get opening balance as of a date
     */
    public function getOpeningBalance(Account $account, $asOfDate): array
    {
        $debits = AccountEntry::where('account_id', $account->id)
            ->where('entry_date', '<', $asOfDate)
            ->sum('debit_amount');

        $credits = AccountEntry::where('account_id', $account->id)
            ->where('entry_date', '<', $asOfDate)
            ->sum('credit_amount');

        $openingDebit = $account->opening_balance_type === 'debit' ? $account->opening_balance : 0;
        $openingCredit = $account->opening_balance_type === 'credit' ? $account->opening_balance : 0;

        $totalDebits = $openingDebit + $debits;
        $totalCredits = $openingCredit + $credits;

        return [
            'balance' => abs($totalDebits - $totalCredits),
            'type' => $totalDebits >= $totalCredits ? 'debit' : 'credit',
        ];
    }

    /**
     * Recalculate all account balances
     */
    public function recalculateAllBalances(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {
            $this->updateAccountBalance($account);
        }

        Log::info('All account balances recalculated');
    }

    /**
     * Remove all ledger entries for a source and refresh affected account balances
     */
    public function removeEntries(string $sourceType, int $sourceId): void
    {
        $accountIds = AccountEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->pluck('account_id')
            ->unique()
            ->filter()
            ->values();

        AccountEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();

        $this->updateBalancesForAccountIds($accountIds->all());
    }

    protected function updateBalancesForAccountIds(array $accountIds): void
    {
        $accounts = Account::whereIn('id', $accountIds)->get();
        foreach ($accounts as $account) {
            $this->updateAccountBalance($account);
        }
    }
}
