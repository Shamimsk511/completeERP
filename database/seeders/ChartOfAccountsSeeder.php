<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\Account;
use App\Models\Accounting\FinancialYear;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Create default financial year
        FinancialYear::firstOrCreate(
            ['name' => 'FY 2025-26'],
            [
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'is_active' => true,
                'is_closed' => false,
            ]
        );

        // ========================================
        // PRIMARY GROUPS (Level 1)
        // ========================================

        $assets = AccountGroup::firstOrCreate(
            ['code' => 'ASSETS'],
            [
                'name' => 'Assets',
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 1,
            ]
        );

        $liabilities = AccountGroup::firstOrCreate(
            ['code' => 'LIABILITIES'],
            [
                'name' => 'Liabilities',
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        $income = AccountGroup::firstOrCreate(
            ['code' => 'INCOME'],
            [
                'name' => 'Income',
                'nature' => 'income',
                'is_system' => true,
                'display_order' => 3,
            ]
        );

        $expenses = AccountGroup::firstOrCreate(
            ['code' => 'EXPENSES'],
            [
                'name' => 'Expenses',
                'nature' => 'expenses',
                'is_system' => true,
                'display_order' => 4,
            ]
        );

        $capital = AccountGroup::firstOrCreate(
            ['code' => 'CAPITAL'],
            [
                'name' => 'Capital Account',
                'nature' => 'capital',
                'is_system' => true,
                'display_order' => 5,
            ]
        );

        // ========================================
        // ASSETS SUB-GROUPS (Level 2)
        // ========================================

        $currentAssets = AccountGroup::firstOrCreate(
            ['code' => 'CURRENT-ASSETS'],
            [
                'name' => 'Current Assets',
                'parent_id' => $assets->id,
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 1,
            ]
        );

        $fixedAssets = AccountGroup::firstOrCreate(
            ['code' => 'FIXED-ASSETS'],
            [
                'name' => 'Fixed Assets',
                'parent_id' => $assets->id,
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        // Current Assets Sub-groups (Level 3)
        $cashInHand = AccountGroup::firstOrCreate(
            ['code' => 'CASH-IN-HAND'],
            [
                'name' => 'Cash-in-Hand',
                'parent_id' => $currentAssets->id,
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 1,
            ]
        );

        $bankAccounts = AccountGroup::firstOrCreate(
            ['code' => 'BANK-ACCOUNTS'],
            [
                'name' => 'Bank Accounts',
                'parent_id' => $currentAssets->id,
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        $sundryDebtors = AccountGroup::firstOrCreate(
            ['code' => 'SUNDRY-DEBTORS'],
            [
                'name' => 'Sundry Debtors',
                'parent_id' => $currentAssets->id,
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 3,
                'description' => 'Customer receivable accounts',
            ]
        );

        $stockInHand = AccountGroup::firstOrCreate(
            ['code' => 'STOCK-IN-HAND'],
            [
                'name' => 'Stock-in-Hand',
                'parent_id' => $currentAssets->id,
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 4,
            ]
        );

        $loansAndAdvances = AccountGroup::firstOrCreate(
            ['code' => 'LOANS-ADVANCES'],
            [
                'name' => 'Loans & Advances (Assets)',
                'parent_id' => $currentAssets->id,
                'nature' => 'assets',
                'is_system' => true,
                'display_order' => 5,
            ]
        );

        // ========================================
        // LIABILITIES SUB-GROUPS (Level 2)
        // ========================================

        $currentLiabilities = AccountGroup::firstOrCreate(
            ['code' => 'CURRENT-LIABILITIES'],
            [
                'name' => 'Current Liabilities',
                'parent_id' => $liabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 1,
            ]
        );

        $longTermLiabilities = AccountGroup::firstOrCreate(
            ['code' => 'LONG-TERM-LIABILITIES'],
            [
                'name' => 'Long Term Liabilities',
                'parent_id' => $liabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        // Current Liabilities Sub-groups (Level 3)
        $sundryCreditors = AccountGroup::firstOrCreate(
            ['code' => 'SUNDRY-CREDITORS'],
            [
                'name' => 'Sundry Creditors',
                'parent_id' => $currentLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 1,
                'description' => 'Supplier payable accounts',
            ]
        );

        $dutiesAndTaxes = AccountGroup::firstOrCreate(
            ['code' => 'DUTIES-TAXES'],
            [
                'name' => 'Duties & Taxes',
                'parent_id' => $currentLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        // Loan-related liability groups
        AccountGroup::firstOrCreate(
            ['code' => 'CC-LOANS'],
            [
                'name' => 'CC Loans',
                'parent_id' => $currentLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 3,
            ]
        );

        AccountGroup::firstOrCreate(
            ['code' => 'DAILY-KISTI'],
            [
                'name' => 'Daily Kisti Loans',
                'parent_id' => $currentLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 4,
            ]
        );

        AccountGroup::firstOrCreate(
            ['code' => 'BANK-LOANS'],
            [
                'name' => 'Bank Loans',
                'parent_id' => $longTermLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 1,
            ]
        );

        AccountGroup::firstOrCreate(
            ['code' => 'PERSONAL-LOANS'],
            [
                'name' => 'Personal Loans',
                'parent_id' => $longTermLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        AccountGroup::firstOrCreate(
            ['code' => 'SME-LOANS'],
            [
                'name' => 'SME Loans',
                'parent_id' => $longTermLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 3,
            ]
        );

        AccountGroup::firstOrCreate(
            ['code' => 'TERM-LOANS'],
            [
                'name' => 'Term Loans',
                'parent_id' => $longTermLiabilities->id,
                'nature' => 'liabilities',
                'is_system' => true,
                'display_order' => 4,
            ]
        );

        // ========================================
        // INCOME SUB-GROUPS (Level 2)
        // ========================================

        $salesAccounts = AccountGroup::firstOrCreate(
            ['code' => 'SALES'],
            [
                'name' => 'Sales Accounts',
                'parent_id' => $income->id,
                'nature' => 'income',
                'is_system' => true,
                'display_order' => 1,
            ]
        );

        $directIncome = AccountGroup::firstOrCreate(
            ['code' => 'DIRECT-INCOME'],
            [
                'name' => 'Direct Income',
                'parent_id' => $income->id,
                'nature' => 'income',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        $indirectIncome = AccountGroup::firstOrCreate(
            ['code' => 'INDIRECT-INCOME'],
            [
                'name' => 'Indirect Income',
                'parent_id' => $income->id,
                'nature' => 'income',
                'is_system' => true,
                'display_order' => 3,
            ]
        );

        // ========================================
        // EXPENSES SUB-GROUPS (Level 2)
        // ========================================

        $purchaseAccounts = AccountGroup::firstOrCreate(
            ['code' => 'PURCHASE'],
            [
                'name' => 'Purchase Accounts',
                'parent_id' => $expenses->id,
                'nature' => 'expenses',
                'affects_gross_profit' => 'yes',
                'is_system' => true,
                'display_order' => 1,
            ]
        );

        $directExpenses = AccountGroup::firstOrCreate(
            ['code' => 'DIRECT-EXPENSES'],
            [
                'name' => 'Direct Expenses',
                'parent_id' => $expenses->id,
                'nature' => 'expenses',
                'affects_gross_profit' => 'yes',
                'is_system' => true,
                'display_order' => 2,
            ]
        );

        $indirectExpenses = AccountGroup::firstOrCreate(
            ['code' => 'INDIRECT-EXPENSES'],
            [
                'name' => 'Indirect Expenses',
                'parent_id' => $expenses->id,
                'nature' => 'expenses',
                'is_system' => true,
                'display_order' => 3,
            ]
        );

        // ========================================
        // DEFAULT ACCOUNTS (Ledgers)
        // ========================================

        // Cash Accounts
        Account::firstOrCreate(
            ['code' => 'CASH-PRIMARY'],
            [
                'name' => 'Cash',
                'account_group_id' => $cashInHand->id,
                'account_type' => 'cash',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'PETTY-CASH'],
            [
                'name' => 'Petty Cash',
                'account_group_id' => $cashInHand->id,
                'account_type' => 'cash',
                'is_system' => false,
            ]
        );

        // Bank Accounts
        Account::firstOrCreate(
            ['code' => 'BANK-PRIMARY'],
            [
                'name' => 'Primary Bank Account',
                'account_group_id' => $bankAccounts->id,
                'account_type' => 'bank',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'MOBILE-BANK'],
            [
                'name' => 'Mobile Banking (bKash/Nagad)',
                'account_group_id' => $bankAccounts->id,
                'account_type' => 'bank',
                'is_system' => true,
            ]
        );

        // Sales Income
        Account::firstOrCreate(
            ['code' => 'SALES-TILES'],
            [
                'name' => 'Sales - Tiles',
                'account_group_id' => $salesAccounts->id,
                'account_type' => 'income',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'SALES-SANITARY'],
            [
                'name' => 'Sales - Sanitary',
                'account_group_id' => $salesAccounts->id,
                'account_type' => 'income',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'SALES-PAINTS'],
            [
                'name' => 'Sales - Paints & Others',
                'account_group_id' => $salesAccounts->id,
                'account_type' => 'income',
                'is_system' => true,
            ]
        );

        // Purchase Expense
        Account::firstOrCreate(
            ['code' => 'PURCHASE-TILES'],
            [
                'name' => 'Purchase - Tiles',
                'account_group_id' => $purchaseAccounts->id,
                'account_type' => 'expense',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'PURCHASE-SANITARY'],
            [
                'name' => 'Purchase - Sanitary',
                'account_group_id' => $purchaseAccounts->id,
                'account_type' => 'expense',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'PURCHASE-PAINTS'],
            [
                'name' => 'Purchase - Paints & Others',
                'account_group_id' => $purchaseAccounts->id,
                'account_type' => 'expense',
                'is_system' => true,
            ]
        );

        // Common Expenses
        Account::firstOrCreate(
            ['code' => 'SALARY-EXPENSE'],
            [
                'name' => 'Salary & Wages',
                'account_group_id' => $indirectExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'RENT-EXPENSE'],
            [
                'name' => 'Rent Expense',
                'account_group_id' => $indirectExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'UTILITY-EXPENSE'],
            [
                'name' => 'Electricity & Utility',
                'account_group_id' => $indirectExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'TRANSPORT-EXPENSE'],
            [
                'name' => 'Transportation & Freight',
                'account_group_id' => $directExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'LABOUR-EXPENSE'],
            [
                'name' => 'Labour & Loading Charges',
                'account_group_id' => $directExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'OTHER-PURCHASE-EXPENSE'],
            [
                'name' => 'Other Purchase Expenses',
                'account_group_id' => $directExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'DISCOUNT-ALLOWED'],
            [
                'name' => 'Discount Allowed',
                'account_group_id' => $indirectExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'INTEREST-EXPENSE'],
            [
                'name' => 'Interest Expense',
                'account_group_id' => $indirectExpenses->id,
                'account_type' => 'expense',
                'is_system' => false,
            ]
        );

        // Capital Account
        Account::firstOrCreate(
            ['code' => 'OWNERS-CAPITAL'],
            [
                'name' => "Owner's Capital",
                'account_group_id' => $capital->id,
                'account_type' => 'capital',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => 'DRAWINGS'],
            [
                'name' => 'Drawings',
                'account_group_id' => $capital->id,
                'account_type' => 'capital',
                'is_system' => false,
            ]
        );

        // Suspense Account
        Account::firstOrCreate(
            ['code' => 'SUSPENSE'],
            [
                'name' => 'Suspense Account',
                'account_group_id' => $currentAssets->id,
                'account_type' => 'suspense',
                'is_system' => true,
                'notes' => 'Used for unidentified transactions',
            ]
        );

        $this->command->info('Chart of Accounts seeded successfully!');
    }
}
