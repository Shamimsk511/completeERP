<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Illuminate\Support\Facades\Log;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     * Creates a ledger account under Sundry Creditors for the vendor/company.
     */
    public function created(Company $company): void
    {
        $this->createOrUpdateCompanyAccount($company);
    }

    /**
     * Handle the Company "updated" event.
     * Updates the linked ledger account name if company name changes.
     */
    public function updated(Company $company): void
    {
        if ($company->isDirty('name') || $company->isDirty('type')) {
            $this->createOrUpdateCompanyAccount($company);
        }
    }

    /**
     * Create or update company/vendor ledger account under Sundry Creditors
     */
    protected function createOrUpdateCompanyAccount(Company $company): void
    {
        try {
            if (!$company->isSupplierType()) {
                $account = Account::where('linkable_type', 'company')
                    ->where('linkable_id', $company->id)
                    ->first();

                if ($account) {
                    $account->update(['is_active' => false]);
                }
                return;
            }

            // Get Sundry Creditors group
            $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

            if (!$sundryCreditors) {
                Log::warning("Sundry Creditors account group not found. Company account not created for {$company->name}");
                return;
            }

            // Check if account already exists
            $account = Account::where('linkable_type', 'company')
                ->where('linkable_id', $company->id)
                ->first();

            if ($account) {
                // Update existing account
                $account->update([
                    'name' => $company->name,
                ]);
                Log::info("Updated supplier account {$account->code} for {$company->name}");
            } else {
                // Create new account
                $account = Account::create([
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
                    'notes' => "Auto-created for supplier/vendor: {$company->name}",
                ]);
                Log::info("Created supplier account {$account->code} for {$company->name}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to create/update company account for {$company->name}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Company "deleted" event.
     * Soft deletes the linked ledger account.
     */
    public function deleted(Company $company): void
    {
        try {
            $account = Account::where('linkable_type', 'company')
                ->where('linkable_id', $company->id)
                ->first();

            if ($account) {
                // Check if account has transactions
                if ($account->accountEntries()->exists()) {
                    // Just deactivate, don't delete
                    $account->update(['is_active' => false]);
                    Log::info("Deactivated supplier account {$account->code} (has transactions)");
                } else {
                    $account->delete();
                    Log::info("Deleted supplier account {$account->code}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to handle company deletion for account: " . $e->getMessage());
        }
    }
}
