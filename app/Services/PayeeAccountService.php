<?php

namespace App\Services;

use App\Models\Payee;
use App\Models\Company;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Services\Accounting\AutoPostingService;
use Illuminate\Support\Facades\Log;

class PayeeAccountService
{
    protected AutoPostingService $autoPostingService;

    public function __construct(AutoPostingService $autoPostingService)
    {
        $this->autoPostingService = $autoPostingService;
    }

    public function ensureAccountForPayee(Payee $payee): ?Account
    {
        if ($payee->account_id) {
            $account = Account::find($payee->account_id);
            if ($account) {
                return $account;
            }
        }

        if ($payee->category === 'supplier' && $payee->company_id) {
            $company = Company::find($payee->company_id);
            if ($company) {
                $account = $this->autoPostingService->getOrCreateSupplierAccount($company);
                $payee->account_id = $account->id;
                $payee->save();
                return $account;
            }
        }

        $group = $this->getAccountGroupForCategory($payee->category ?: $payee->type ?: 'supplier');
        if (!$group) {
            Log::warning("Account group not found for payee {$payee->id} category {$payee->category}");
            return null;
        }

        $accountType = $payee->category === 'supplier' ? 'supplier' : 'liability';

        $account = Account::create([
            'name' => $payee->name,
            'code' => 'PAY-' . str_pad($payee->id, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $group->id,
            'account_type' => $accountType,
            'opening_balance' => 0,
            'opening_balance_type' => 'credit',
            'current_balance' => 0,
            'current_balance_type' => 'credit',
            'linkable_type' => 'payee',
            'linkable_id' => $payee->id,
            'is_active' => true,
            'is_system' => false,
            'notes' => "Auto-created for payee: {$payee->name}",
        ]);

        $payee->account_id = $account->id;
        $payee->save();

        return $account;
    }

    public function getAccountGroupForCategory(string $category): ?AccountGroup
    {
        $category = $category ?: 'supplier';

        $liabilities = AccountGroup::firstOrCreate(
            ['code' => 'LIABILITIES'],
            [
                'name' => 'Liabilities',
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        $currentLiabilities = $this->ensureChildGroup('CURRENT-LIABILITIES', 'Current Liabilities', $liabilities, 'liabilities', 1);
        $longTermLiabilities = $this->ensureChildGroup('LONG-TERM-LIABILITIES', 'Long Term Liabilities', $liabilities, 'liabilities', 2);

        if (!$currentLiabilities || !$longTermLiabilities) {
            return null;
        }

        return match ($category) {
            'supplier' => $this->ensureChildGroup('SUNDRY-CREDITORS', 'Sundry Creditors', $currentLiabilities, 'liabilities', 1),
            'cc' => $this->ensureChildGroup('CC-LOANS', 'CC Loans', $currentLiabilities, 'liabilities', 2),
            'daily_kisti' => $this->ensureChildGroup('DAILY-KISTI', 'Daily Kisti Loans', $currentLiabilities, 'liabilities', 3),
            'bank' => $this->ensureChildGroup('BANK-LOANS', 'Bank Loans', $longTermLiabilities, 'liabilities', 1),
            'personal' => $this->ensureChildGroup('PERSONAL-LOANS', 'Personal Loans', $longTermLiabilities, 'liabilities', 2),
            'sme' => $this->ensureChildGroup('SME-LOANS', 'SME Loans', $longTermLiabilities, 'liabilities', 3),
            'term_loan' => $this->ensureChildGroup('TERM-LOANS', 'Term Loans', $longTermLiabilities, 'liabilities', 4),
            default => $this->ensureChildGroup('SUNDRY-CREDITORS', 'Sundry Creditors', $currentLiabilities, 'liabilities', 1),
        };
    }

    public function ensureInterestExpenseAccount(): ?Account
    {
        $expenses = AccountGroup::firstOrCreate(
            ['code' => 'EXPENSES'],
            [
                'name' => 'Expenses',
                'nature' => 'expenses',
                'is_system' => true,
                'display_order' => 4,
            ]
        );

        $indirectExpenses = $this->ensureChildGroup('INDIRECT-EXPENSES', 'Indirect Expenses', $expenses, 'expenses', 3);
        if (!$indirectExpenses) {
            return null;
        }

        return Account::firstOrCreate(
            ['code' => 'INTEREST-EXPENSE'],
            [
                'name' => 'Interest Expense',
                'account_group_id' => $indirectExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );
    }

    protected function ensureChildGroup(string $code, string $name, ?AccountGroup $parent, string $nature, int $displayOrder): ?AccountGroup
    {
        if (!$parent) {
            return null;
        }

        return AccountGroup::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'parent_id' => $parent->id,
                'nature' => $nature,
                'is_system' => true,
                'display_order' => $displayOrder,
            ]
        );
    }
}
