<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\PaymentAllocationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecalculateCustomerBalances extends Command
{
    protected $signature = 'customers:recalculate-balances
        {--customer= : Specific customer ID}
        {--chunk=500 : Chunk size for batch processing}
        {--dry-run : Show what would change without saving}';

    protected $description = 'Recalculate customer balances and invoice allocations from transactions';

    public function handle(PaymentAllocationService $paymentAllocationService): int
    {
        $customerId = $this->option('customer');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $query = Customer::query()->select(['id', 'outstanding_balance']);
        if ($customerId) {
            $query->where('id', $customerId);
        }

        $total = (int) $query->count();
        if ($total === 0) {
            $this->info('No customers found.');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('DRY RUN - no changes will be saved.');
        }

        $this->info("Recalculating balances for {$total} customer(s)...");

        $processed = 0;
        $changed = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('id')->chunkById($chunkSize, function ($customers) use (
            $paymentAllocationService,
            $dryRun,
            &$processed,
            &$changed,
            &$failed,
            $bar
        ) {
            foreach ($customers as $customer) {
                $processed++;
                $before = (float) ($customer->outstanding_balance ?? 0);

                try {
                    if ($dryRun) {
                        DB::beginTransaction();
                        $paymentAllocationService->allocatePayments($customer->id);
                        $after = (float) (Customer::find($customer->id)->outstanding_balance ?? 0);
                        DB::rollBack();
                    } else {
                        $paymentAllocationService->allocatePayments($customer->id);
                        $after = (float) (Customer::find($customer->id)->outstanding_balance ?? 0);
                    }

                    if (abs($after - $before) > 0.01) {
                        $changed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                    $this->error("Failed customer {$customer->id}: {$e->getMessage()}");
                    if ($dryRun && DB::transactionLevel() > 0) {
                        DB::rollBack();
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Processed: {$processed}");
        $this->info("Changed: {$changed}");
        if ($failed > 0) {
            $this->error("Failed: {$failed}");
            return Command::FAILURE;
        }

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
