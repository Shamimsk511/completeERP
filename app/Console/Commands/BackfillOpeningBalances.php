<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Payee;
use App\Models\Tenant;
use App\Models\Accounting\AccountEntry;
use App\Services\PayeeAccountService;
use App\Services\Accounting\OpeningBalanceService;
use App\Support\TenantContext;
use Illuminate\Console\Command;

class BackfillOpeningBalances extends Command
{
    protected $signature = 'accounting:backfill-opening-balances 
        {--tenant= : Tenant ID to process (default: all tenants)} 
        {--dry-run : Show what would be done without changes}
        {--force : Force backfill even if ledger entries exist}
        {--payees : Backfill payee opening balances only}
        {--companies : Backfill company opening balances only}';

    protected $description = 'Backfill opening balances for payees/companies';

    public function handle(): int
    {
        $originalTenantId = TenantContext::currentId();
        $tenantId = $this->option('tenant');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $onlyPayees = (bool) $this->option('payees');
        $onlyCompanies = (bool) $this->option('companies');
        $processPayees = $onlyPayees || (!$onlyPayees && !$onlyCompanies);
        $processCompanies = $onlyCompanies || (!$onlyPayees && !$onlyCompanies);

        $tenants = Tenant::query();
        if ($tenantId) {
            $tenants->where('id', $tenantId);
        }
        $tenants = $tenants->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            TenantContext::set($tenant->id);
            $this->info("Processing tenant {$tenant->id} - {$tenant->name}");

            if ($processPayees) {
                $this->backfillPayees($dryRun, $force);
            }

            if ($processCompanies) {
                $this->backfillCompanies($dryRun, $force);
            }
        }

        if ($originalTenantId) {
            TenantContext::set($originalTenantId);
        } else {
            TenantContext::clear();
        }

        return self::SUCCESS;
    }

    protected function backfillPayees(bool $dryRun, bool $force): void
    {
        $openingBalanceService = app(OpeningBalanceService::class);
        $accountService = app(PayeeAccountService::class);

        $payees = Payee::with('account')->get();
        foreach ($payees as $payee) {
            $account = $payee->account ?: $accountService->ensureAccountForPayee($payee);

            $balance = $payee->opening_balance ?? 0;
            if (abs($balance) < 0.0001) {
                $balance = $payee->current_balance ?? 0;
            }
            if (abs($balance) < 0.0001 && ($payee->principal_balance ?? 0) > 0) {
                $balance = $payee->principal_balance;
            }
            if (abs($balance) < 0.0001 && ($account->opening_balance ?? 0) != 0) {
                $balance = $account->opening_balance;
            }

            if (abs($balance) < 0.0001) {
                continue;
            }

            $hasEntries = AccountEntry::where('account_id', $account->id)->exists();
            if ($hasEntries && !$force) {
                $this->line(" - Payee {$payee->id} skipped (has ledger entries)");
                continue;
            }

            $balanceType = $balance < 0 ? 'debit' : 'credit';
            $amount = abs($balance);

            if ($dryRun) {
                $this->line(" - Payee {$payee->id} opening balance: {$amount} ({$balanceType})");
                continue;
            }

            $account->opening_balance = 0;
            $account->current_balance = $hasEntries ? $account->current_balance : 0;
            $account->save();

            $openingBalanceService->postPayeeOpeningBalance($payee, $amount, $balanceType, now()->toDateString(), null);
            $this->line(" - Payee {$payee->id} opening balance updated");
        }
    }

    protected function backfillCompanies(bool $dryRun, bool $force): void
    {
        $openingBalanceService = app(OpeningBalanceService::class);

        $companies = Company::all();
        foreach ($companies as $company) {
            if (!$company->isSupplierType()) {
                continue;
            }

            $balance = $company->opening_balance ?? 0;
            if (abs($balance) < 0.0001) {
                continue;
            }

            $balanceType = $company->opening_balance_type ?: 'credit';
            $amount = abs($balance);

            if ($dryRun) {
                $this->line(" - Company {$company->id} opening balance: {$amount} ({$balanceType})");
                continue;
            }

            $openingBalanceService->postCompanyOpeningBalance($company, $amount, $balanceType, now()->toDateString(), null);
            $this->line(" - Company {$company->id} opening balance updated");
        }
    }
}
