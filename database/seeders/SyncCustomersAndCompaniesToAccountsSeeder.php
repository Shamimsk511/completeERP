<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Illuminate\Support\Facades\DB;

class SyncCustomersAndCompaniesToAccountsSeeder extends Seeder
{
    /**
     * Sync all existing customers and companies to their ledger accounts.
     * - Customers -> Sundry Debtors
     * - Companies (Vendors) -> Sundry Creditors
     */
    public function run(): void
    {
        $this->command->info('Starting sync of customers and companies to accounting ledgers...');

        // Get account groups
        $sundryDebtors = AccountGroup::where('code', 'SUNDRY-DEBTORS')->first();
        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

        if (!$sundryDebtors) {
            $this->command->error('Sundry Debtors account group not found. Please run ChartOfAccountsSeeder first.');
            return;
        }

        if (!$sundryCreditors) {
            $this->command->error('Sundry Creditors account group not found. Please run ChartOfAccountsSeeder first.');
            return;
        }

        // Sync Customers to Sundry Debtors
        $this->syncCustomers($sundryDebtors);

        // Sync Companies (Vendors) to Sundry Creditors
        $this->syncCompanies($sundryCreditors);

        $this->command->info('Sync completed successfully!');
    }

    /**
     * Sync all customers to Sundry Debtors accounts
     */
    protected function syncCustomers(AccountGroup $sundryDebtors): void
    {
        $customers = Customer::all();
        $created = 0;
        $skipped = 0;

        $this->command->info("Found {$customers->count()} customers to sync...");

        foreach ($customers as $customer) {
            // Check if account already exists
            $existingAccount = Account::where('linkable_type', 'customer')
                ->where('linkable_id', $customer->id)
                ->first();

            if ($existingAccount) {
                // Update name if changed
                if ($existingAccount->name !== $customer->name) {
                    $existingAccount->update(['name' => $customer->name]);
                }
                $skipped++;
                continue;
            }

            // Create new account
            $openingBalance = $customer->opening_balance ?? 0;

            Account::create([
                'name' => $customer->name,
                'code' => 'CUST-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
                'account_group_id' => $sundryDebtors->id,
                'account_type' => 'customer',
                'opening_balance' => $openingBalance,
                'opening_balance_type' => 'debit',
                'current_balance' => $openingBalance,
                'current_balance_type' => 'debit',
                'linkable_type' => 'customer',
                'linkable_id' => $customer->id,
                'is_active' => true,
                'is_system' => false,
                'notes' => "Auto-synced from existing customer",
            ]);

            $created++;
        }

        $this->command->info("Customers: {$created} created, {$skipped} already existed");
    }

    /**
     * Sync all companies (vendors) to Sundry Creditors accounts
     */
    protected function syncCompanies(AccountGroup $sundryCreditors): void
    {
        $companies = Company::all();
        $created = 0;
        $skipped = 0;

        $this->command->info("Found {$companies->count()} companies/vendors to sync...");

        foreach ($companies as $company) {
            // Check if account already exists
            $existingAccount = Account::where('linkable_type', 'company')
                ->where('linkable_id', $company->id)
                ->first();

            if ($existingAccount) {
                // Update name if changed
                if ($existingAccount->name !== $company->name) {
                    $existingAccount->update(['name' => $company->name]);
                }
                $skipped++;
                continue;
            }

            // Create new account
            Account::create([
                'name' => $company->name,
                'code' => 'SUPP-' . str_pad($company->id, 5, '0', STR_PAD_LEFT),
                'account_group_id' => $sundryCreditors->id,
                'account_type' => 'supplier',
                'opening_balance' => 0,
                'opening_balance_type' => 'credit',
                'current_balance' => 0,
                'current_balance_type' => 'credit',
                'linkable_type' => 'company',
                'linkable_id' => $company->id,
                'is_active' => true,
                'is_system' => false,
                'notes' => "Auto-synced from existing vendor/company",
            ]);

            $created++;
        }

        $this->command->info("Companies/Vendors: {$created} created, {$skipped} already existed");
    }
}
