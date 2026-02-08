<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     * Creates a ledger account under Sundry Debtors for the customer.
     */
    public function created(Customer $customer): void
    {
        $this->createOrUpdateCustomerAccount($customer);
    }

    /**
     * Handle the Customer "updated" event.
     * Updates the linked ledger account name/group if customer details change.
     */
    public function updated(Customer $customer): void
    {
        if ($customer->isDirty('name') || $customer->isDirty('opening_balance') || $customer->isDirty('account_group_id')) {
            $this->createOrUpdateCustomerAccount($customer);
        }
    }

    /**
     * Create or update customer ledger account under Sundry Debtors or selected group
     */
    protected function createOrUpdateCustomerAccount(Customer $customer): void
    {
        try {
            // Determine which account group to use
            $accountGroup = null;

            // If customer has a specific account group, use it
            if ($customer->account_group_id) {
                $accountGroup = AccountGroup::find($customer->account_group_id);
            }

            // Fall back to Sundry Debtors if no specific group
            if (!$accountGroup) {
                $accountGroup = AccountGroup::where('code', 'SUNDRY-DEBTORS')->first();
            }

            if (!$accountGroup) {
                Log::warning("No account group found for customer. Customer account not created for {$customer->name}");
                return;
            }

            // Check if account already exists
            $account = Account::where('linkable_type', 'customer')
                ->where('linkable_id', $customer->id)
                ->first();

            $openingBalance = $customer->opening_balance ?? 0;

            if ($account) {
                // Update existing account
                $account->update([
                    'name' => $customer->name,
                    'opening_balance' => $openingBalance,
                    'account_group_id' => $accountGroup->id,
                ]);
                Log::info("Updated customer account {$account->code} for {$customer->name} in group {$accountGroup->name}");
            } else {
                // Create new account
                $account = Account::create([
                    'name' => $customer->name,
                    'code' => 'CUST-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
                    'account_group_id' => $accountGroup->id,
                    'account_type' => 'customer',
                    'opening_balance' => $openingBalance,
                    'opening_balance_type' => 'debit',
                    'current_balance' => $openingBalance,
                    'current_balance_type' => 'debit',
                    'linkable_type' => 'customer',
                    'linkable_id' => $customer->id,
                    'is_active' => true,
                    'is_system' => false,
                    'notes' => "Auto-created for customer: {$customer->name}",
                ]);
                Log::info("Created customer account {$account->code} for {$customer->name} in group {$accountGroup->name}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to create/update customer account for {$customer->name}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Customer "deleted" event.
     * Soft deletes the linked ledger account.
     */
    public function deleted(Customer $customer): void
    {
        try {
            $account = Account::where('linkable_type', 'customer')
                ->where('linkable_id', $customer->id)
                ->first();

            if ($account) {
                // Check if account has transactions
                if ($account->accountEntries()->exists()) {
                    // Just deactivate, don't delete
                    $account->update(['is_active' => false]);
                    Log::info("Deactivated customer account {$account->code} (has transactions)");
                } else {
                    $account->delete();
                    Log::info("Deleted customer account {$account->code}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to handle customer deletion for account: " . $e->getMessage());
        }
    }
}
