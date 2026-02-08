<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Console\Command;

class RecalculateInvoiceSnapshots extends Command
{
    protected $signature = 'invoices:recalculate-snapshots {--customer= : Specific customer ID} {--dry-run : Show what would change without saving}';

    protected $description = 'Recalculate snapshot fields (previous_balance, initial_paid_amount) for existing invoices';

    public function handle()
    {
        $customerId = $this->option('customer');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be saved');
        }

        $query = Invoice::orderBy('customer_id')->orderBy('invoice_date')->orderBy('id');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->info('No invoices found.');
            return 0;
        }

        $this->info("Processing {$invoices->count()} invoices...");

        $currentCustomerId = null;
        $runningBalance = 0;
        $updated = 0;

        $this->withProgressBar($invoices, function ($invoice) use (&$currentCustomerId, &$runningBalance, &$updated, $dryRun) {
            // Reset running balance when customer changes
            if ($currentCustomerId !== $invoice->customer_id) {
                $currentCustomerId = $invoice->customer_id;
                $customer = Customer::find($invoice->customer_id);
                // Start with customer's opening balance
                $runningBalance = $customer ? ($customer->opening_balance ?? 0) : 0;
            }

            // Get the payment amount made at invoice time (transactions linked to this invoice)
            $initialPayment = (float) Transaction::where('invoice_id', $invoice->id)
                ->where('type', 'debit')
                ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
                ->value('total');

            // Only update if values are different
            $previousBalanceChanged = abs(($invoice->previous_balance ?? 0) - $runningBalance) > 0.01;
            $initialPaidChanged = abs(($invoice->initial_paid_amount ?? 0) - $initialPayment) > 0.01;

            if ($previousBalanceChanged || $initialPaidChanged) {
                if (!$dryRun) {
                    $invoice->update([
                        'previous_balance' => $runningBalance,
                        'initial_paid_amount' => $initialPayment,
                    ]);
                }
                $updated++;
            }

            // Update running balance for next invoice
            // Add this invoice's total and subtract payments made
            $runningBalance += $invoice->total;
            $runningBalance -= $initialPayment;
        });

        $this->newLine();
        $this->info("Updated {$updated} invoices.");

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return 0;
    }
}
