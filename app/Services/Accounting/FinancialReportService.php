<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\AccountEntry;
use App\Models\Payee;
use App\Models\Product;
use App\Models\Company;
use App\Support\Math;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
    }

    /**
     * Generate Trial Balance
     */
    public function getTrialBalance($asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        $accounts = Account::with('accountGroup')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $trialBalance = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalanceAsOf($account, $asOfDate);

            if ($balance['balance'] == 0) {
                continue;
            }

            $debit = $balance['type'] === 'debit' ? $balance['balance'] : 0;
            $credit = $balance['type'] === 'credit' ? $balance['balance'] : 0;

            $trialBalance[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'group' => $account->accountGroup->name,
                'nature' => $account->accountGroup->nature,
                'debit' => $debit,
                'credit' => $credit,
            ];

            $totalDebits += $debit;
            $totalCredits += $credit;
        }

        return [
            'date' => $asOfDate,
            'accounts' => $trialBalance,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'difference' => abs($totalDebits - $totalCredits),
            'is_balanced' => Math::compareMoney($totalDebits, $totalCredits, 2) === 0,
        ];
    }

    /**
     * Generate Balance Sheet
     */
    public function getBalanceSheet($asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        $assets = $this->getGroupBalances('assets', $asOfDate);
        $liabilities = $this->getGroupBalances('liabilities', $asOfDate);
        $capital = $this->getGroupBalances('capital', $asOfDate);

        // Calculate P&L for the period (retained earnings)
        $profitLoss = $this->calculateProfitLoss(null, $asOfDate);

        $computedInventory = $this->getComputedInventoryValue();
        $computedPayeeLiabilities = $this->getComputedPayeeBalance($asOfDate);

        $totalAssets = collect($assets)->sum('balance') + $computedInventory;
        $totalLiabilities = collect($liabilities)->sum('balance') + $computedPayeeLiabilities;
        $totalCapital = collect($capital)->sum('balance') + $profitLoss['net_profit'];

        return [
            'date' => $asOfDate,
            'assets' => [
                'groups' => $assets,
                'total' => $totalAssets,
                'computed' => [
                    [
                        'label' => 'Inventory (Computed)',
                        'amount' => $computedInventory,
                    ],
                ],
            ],
            'liabilities' => [
                'groups' => $liabilities,
                'total' => $totalLiabilities,
                'computed' => [
                    [
                        'label' => 'Payees Balance (Computed)',
                        'amount' => $computedPayeeLiabilities,
                    ],
                ],
            ],
            'capital' => [
                'groups' => $capital,
                'profit_loss' => $profitLoss['net_profit'],
                'total' => $totalCapital,
            ],
            'liabilities_and_capital' => $totalLiabilities + $totalCapital,
            'difference' => abs($totalAssets - ($totalLiabilities + $totalCapital)),
            'is_balanced' => Math::compareMoney($totalAssets, $totalLiabilities + $totalCapital, 2) === 0,
        ];
    }

    protected function getComputedInventoryValue(): float
    {
        $total = Product::query()
            ->selectRaw('SUM(CASE WHEN COALESCE(current_stock, 0) > 0 THEN COALESCE(current_stock, 0) ELSE 0 END * COALESCE(purchase_price, 0)) as total')
            ->value('total');

        return (float) ($total ?? 0);
    }

    protected function getComputedPayeeBalance($asOfDate = null): float
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        $cashInSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_in_total'))
            ->where('transaction_type', 'cash_in')
            ->whereDate('transaction_date', '<=', $asOfDate)
            ->groupBy('payee_id');
        $cashOutSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_out_total'))
            ->where('transaction_type', 'cash_out')
            ->whereDate('transaction_date', '<=', $asOfDate)
            ->groupBy('payee_id');

        $payees = Payee::query()
            ->leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_in.payee_id');
            })
            ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_out.payee_id');
            })
            ->get([
                'payees.id',
                'payees.current_balance',
                'payees.opening_balance',
                DB::raw('COALESCE(payee_cash_in.cash_in_total, 0) as cash_in_total'),
                DB::raw('COALESCE(payee_cash_out.cash_out_total, 0) as cash_out_total'),
            ]);

        $suppliers = Company::suppliers()
            ->with('ledgerAccount:id,linkable_id,opening_balance,opening_balance_type')
            ->get(['id', 'opening_balance', 'opening_balance_type']);

        $accountIds = $payees->pluck('account_id')
            ->filter()
            ->merge($suppliers->pluck('ledgerAccount.id')->filter())
            ->unique()
            ->values()
            ->all();

        $debitsByAccount = collect();
        $creditsByAccount = collect();
        if (!empty($accountIds)) {
            $debitsByAccount = AccountEntry::query()
                ->select('account_id', DB::raw('SUM(debit_amount) as total_debit'))
                ->whereIn('account_id', $accountIds)
                ->where('entry_date', '<=', $asOfDate)
                ->groupBy('account_id')
                ->pluck('total_debit', 'account_id');

            $creditsByAccount = AccountEntry::query()
                ->select('account_id', DB::raw('SUM(credit_amount) as total_credit'))
                ->whereIn('account_id', $accountIds)
                ->where('entry_date', '<=', $asOfDate)
                ->groupBy('account_id')
                ->pluck('total_credit', 'account_id');
        }

        $accountSignedBalance = function (?Account $account) use ($creditsByAccount, $debitsByAccount): ?float {
            if (!$account) {
                return null;
            }

            $opening = (float) ($account->opening_balance ?? 0);
            $openingType = $account->opening_balance_type ?? 'credit';
            $signedOpening = $openingType === 'credit' ? $opening : -$opening;
            $credits = (float) ($creditsByAccount[$account->id] ?? 0);
            $debits = (float) ($debitsByAccount[$account->id] ?? 0);

            return $signedOpening + $credits - $debits;
        };

        $total = 0.0;
        foreach ($payees as $payee) {
            $opening = (float) ($payee->opening_balance ?? 0);
            $cashIn = (float) ($payee->cash_in_total ?? 0);
            $cashOut = (float) ($payee->cash_out_total ?? 0);
            $signed = $opening + $cashOut - $cashIn;

            if (abs($signed) < 0.0001 && abs($opening) < 0.0001 && abs($cashIn) < 0.0001 && abs($cashOut) < 0.0001) {
                $legacy = (float) ($payee->current_balance ?? 0);
                if (abs($legacy) > 0.0001) {
                    $signed = $legacy;
                }
            }

            if ($signed > 0) {
                $total += $signed;
            }
        }

        foreach ($suppliers as $company) {
            $signed = $accountSignedBalance($company->ledgerAccount);
            if ($signed === null || abs($signed) < 0.0001) {
                $opening = (float) ($company->opening_balance ?? 0);
                $openingType = $company->opening_balance_type ?? 'credit';
                $signed = $openingType === 'credit' ? $opening : -$opening;
            }

            if ($signed > 0) {
                $total += $signed;
            }
        }

        return $total;
    }

    /**
     * Generate Profit & Loss Statement
     */
    public function getProfitAndLoss($fromDate, $toDate): array
    {
        return $this->calculateProfitLoss($fromDate, $toDate);
    }

    /**
     * Calculate Profit/Loss for period
     */
    protected function calculateProfitLoss($fromDate, $toDate): array
    {
        $income = $this->getGroupBalances('income', $toDate, $fromDate);
        $expenses = $this->getGroupBalances('expenses', $toDate, $fromDate);

        $totalIncome = collect($income)->sum('balance');
        $totalExpenses = collect($expenses)->sum('balance');

        // Calculate gross profit (sales minus direct expenses)
        $grossProfit = $this->calculateGrossProfit($fromDate, $toDate);

        $netProfit = $totalIncome - $totalExpenses;

        return [
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'income' => [
                'groups' => $income,
                'total' => $totalIncome,
            ],
            'expenses' => [
                'groups' => $expenses,
                'total' => $totalExpenses,
            ],
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'is_profit' => $netProfit >= 0,
        ];
    }

    /**
     * Calculate Gross Profit
     */
    protected function calculateGrossProfit($fromDate, $toDate): float
    {
        // Sales
        $salesGroup = AccountGroup::where('code', 'SALES')->first();
        $sales = 0;
        if ($salesGroup) {
            foreach ($salesGroup->all_accounts as $account) {
                $balance = $this->getAccountBalanceForPeriod($account, $fromDate, $toDate);
                $sales += $balance['balance'];
            }
        }

        // Cost of goods sold (direct expenses)
        $directExpensesGroups = AccountGroup::where('affects_gross_profit', 'yes')
            ->where('nature', 'expenses')
            ->get();

        $cogs = 0;
        foreach ($directExpensesGroups as $group) {
            foreach ($group->all_accounts as $account) {
                $balance = $this->getAccountBalanceForPeriod($account, $fromDate, $toDate);
                $cogs += $balance['balance'];
            }
        }

        return $sales - $cogs;
    }

    /**
     * Get Day Book (all ledger entries for a date)
     */
    public function getDayBook($date): array
    {
        $entries = AccountEntry::with('account')
            ->whereDate('entry_date', $date)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $summary = [
            'total_entries' => $entries->count(),
            'total_debits' => $entries->sum('debit_amount'),
            'total_credits' => $entries->sum('credit_amount'),
            'by_type' => $entries->groupBy('source_type')->map->count()->toArray(),
        ];

        $formattedEntries = $entries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'reference' => $entry->reference ?: strtoupper((string) $entry->source_type) . '-' . $entry->source_id,
                'source_type' => $entry->source_type,
                'description' => $entry->description,
                'debit' => $entry->debit_amount,
                'credit' => $entry->credit_amount,
                'account' => $entry->account?->name,
            ];
        })->toArray();

        return [
            'date' => $date,
            'entries' => $formattedEntries,
            'summary' => $summary,
        ];
    }

    /**
     * Get Cash Book
     */
    public function getCashBook($fromDate, $toDate): array
    {
        $cashAccounts = Account::where('account_type', 'cash')->get();

        $entries = AccountEntry::with('account')
            ->whereIn('account_id', $cashAccounts->pluck('id'))
            ->whereBetween('entry_date', [$fromDate, $toDate])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        // Calculate opening balance for cash accounts
        $openingBalance = 0;
        foreach ($cashAccounts as $account) {
            $ob = $this->glService->getOpeningBalance($account, $fromDate);
            $openingBalance += $ob['type'] === 'debit' ? $ob['balance'] : -$ob['balance'];
        }

        // Calculate running balance
        $runningBalance = $openingBalance;
        $entriesWithBalance = [];

        foreach ($entries as $entry) {
            $runningBalance += $entry->debit_amount - $entry->credit_amount;

            $entriesWithBalance[] = [
                'date' => $entry->entry_date,
                'reference' => $entry->reference ?: strtoupper((string) $entry->source_type) . '-' . $entry->source_id,
                'source_type' => $entry->source_type,
                'particulars' => $entry->description,
                'account' => $entry->account->name,
                'receipt' => $entry->debit_amount,
                'payment' => $entry->credit_amount,
                'balance' => $runningBalance,
            ];
        }

        return [
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'opening_balance' => $openingBalance,
            'entries' => $entriesWithBalance,
            'closing_balance' => $runningBalance,
            'totals' => [
                'receipts' => $entries->sum('debit_amount'),
                'payments' => $entries->sum('credit_amount'),
            ],
        ];
    }

    /**
     * Get Bank Book
     */
    public function getBankBook($fromDate, $toDate, $bankAccountId = null): array
    {
        $query = Account::where('account_type', 'bank');
        if ($bankAccountId) {
            $query->where('id', $bankAccountId);
        }
        $bankAccounts = $query->get();

        $entries = AccountEntry::with('account')
            ->whereIn('account_id', $bankAccounts->pluck('id'))
            ->whereBetween('entry_date', [$fromDate, $toDate])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        // Calculate opening balance
        $openingBalance = 0;
        foreach ($bankAccounts as $account) {
            $ob = $this->glService->getOpeningBalance($account, $fromDate);
            $openingBalance += $ob['type'] === 'debit' ? $ob['balance'] : -$ob['balance'];
        }

        // Calculate running balance
        $runningBalance = $openingBalance;
        $entriesWithBalance = [];

        foreach ($entries as $entry) {
            $runningBalance += $entry->debit_amount - $entry->credit_amount;

            $entriesWithBalance[] = [
                'date' => $entry->entry_date,
                'reference' => $entry->reference ?: strtoupper((string) $entry->source_type) . '-' . $entry->source_id,
                'source_type' => $entry->source_type,
                'particulars' => $entry->description,
                'account' => $entry->account->name,
                'deposit' => $entry->debit_amount,
                'withdrawal' => $entry->credit_amount,
                'balance' => $runningBalance,
            ];
        }

        return [
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'bank_accounts' => $bankAccounts,
            'opening_balance' => $openingBalance,
            'entries' => $entriesWithBalance,
            'closing_balance' => $runningBalance,
            'totals' => [
                'deposits' => $entries->sum('debit_amount'),
                'withdrawals' => $entries->sum('credit_amount'),
            ],
        ];
    }

    /**
     * Get account balance as of a specific date
     */
    protected function getAccountBalanceAsOf(Account $account, $asOfDate): array
    {
        $debits = AccountEntry::where('account_id', $account->id)
            ->where('entry_date', '<=', $asOfDate)
            ->sum('debit_amount');

        $credits = AccountEntry::where('account_id', $account->id)
            ->where('entry_date', '<=', $asOfDate)
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
     * Get account balance for a date range (for P&L)
     */
    protected function getAccountBalanceForPeriod(Account $account, $fromDate, $toDate): array
    {
        $query = AccountEntry::where('account_id', $account->id);
        if ($fromDate) {
            $query->whereBetween('entry_date', [$fromDate, $toDate]);
        } else {
            $query->where('entry_date', '<=', $toDate);
        }

        $debits = (clone $query)->sum('debit_amount');
        $credits = (clone $query)->sum('credit_amount');

        // For income/expense, we don't include opening balance in P&L
        return [
            'balance' => abs($debits - $credits),
            'type' => $debits >= $credits ? 'debit' : 'credit',
        ];
    }

    /**
     * Get balances for all accounts in a nature group
     */
    protected function getGroupBalances(string $nature, $asOfDate, $fromDate = null): array
    {
        $groups = AccountGroup::where('nature', $nature)
            ->whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('display_order')
            ->get();

        $result = [];
        foreach ($groups as $group) {
            $groupBalance = $this->getGroupTotalBalance($group, $asOfDate, $fromDate);
            if ($groupBalance > 0) {
                $result[] = [
                    'group_id' => $group->id,
                    'group' => $group->name,
                    'code' => $group->code,
                    'balance' => $groupBalance,
                    'children' => $this->getChildGroupBalances($group, $asOfDate, $fromDate),
                ];
            }
        }

        return $result;
    }

    /**
     * Get total balance for a group including all nested accounts
     */
    protected function getGroupTotalBalance(AccountGroup $group, $asOfDate, $fromDate = null): float
    {
        $total = 0;

        foreach ($group->accounts as $account) {
            if ($fromDate) {
                $balance = $this->getAccountBalanceForPeriod($account, $fromDate, $asOfDate);
            } else {
                $balance = $this->getAccountBalanceAsOf($account, $asOfDate);
            }
            $total += $balance['balance'];
        }

        foreach ($group->children as $child) {
            $total += $this->getGroupTotalBalance($child, $asOfDate, $fromDate);
        }

        return $total;
    }

    /**
     * Get balances for child groups
     */
    protected function getChildGroupBalances(AccountGroup $group, $asOfDate, $fromDate = null): array
    {
        $children = [];

        foreach ($group->children as $child) {
            $balance = $this->getGroupTotalBalance($child, $asOfDate, $fromDate);
            if ($balance > 0) {
                $children[] = [
                    'group_id' => $child->id,
                    'group' => $child->name,
                    'code' => $child->code,
                    'balance' => $balance,
                    'children' => $this->getChildGroupBalances($child, $asOfDate, $fromDate),
                ];
            }
        }

        return $children;
    }
}
