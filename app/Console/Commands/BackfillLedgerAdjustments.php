<?php

namespace App\Console\Commands;

use App\Models\Accounting\AccountEntry;
use App\Models\Customer;
use App\Models\Transaction;
use App\Services\Accounting\AutoPostingService;
use App\Services\PaymentAllocationService;
use Illuminate\Console\Command;

class BackfillLedgerAdjustments extends Command
{
    protected $signature = 'ledger:backfill-adjustments {--from=} {--to=} {--chunk=200} {--dry-run} {--no-progress} {--max-seconds=} {--stage=} {--transaction-after=} {--customer-after=}';

    protected $description = 'Backfill customer ledger adjustments/returns from transactions, then sync ledger balances.';

    public function handle(
        AutoPostingService $autoPostingService,
        PaymentAllocationService $paymentAllocationService
    ): int {
        $from = $this->option('from');
        $to = $this->option('to');
        $chunk = (int) ($this->option('chunk') ?: 200);
        $dryRun = (bool) $this->option('dry-run');
        $showProgress = !$this->option('no-progress');
        $maxSeconds = (int) ($this->option('max-seconds') ?: 0);
        $stage = $this->option('stage');
        $stage = is_string($stage) ? strtolower(trim($stage)) : null;
        $transactionAfter = (int) ($this->option('transaction-after') ?: 0);
        $customerAfter = (int) ($this->option('customer-after') ?: 0);
        $startedAt = microtime(true);
        $runFull = !$stage;
        $runAdjustments = $runFull || $stage === 'adjustments';
        $runSync = $runFull || $stage === 'sync';

        if ($dryRun) {
            $this->warn('DRY RUN - no changes will be saved.');
        }

        if ($runAdjustments) {
            $this->info('Backfilling adjustment/return ledger entries...');

            $transactionQuery = Transaction::query()
                ->whereNull('invoice_id')
                ->orderBy('id');

            if ($from) {
                $transactionQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $transactionQuery->whereDate('created_at', '<=', $to);
            }
            if ($transactionAfter > 0) {
                $transactionQuery->where('id', '>', $transactionAfter);
            }

            $totalTransactions = $transactionQuery->count();
            $created = 0;
            $skipped = 0;
            $errors = 0;
            $processed = 0;
            $lastTransactionId = $transactionAfter;
            $stop = false;

            $transactionQuery->chunkById($chunk, function ($transactions) use (
                &$created,
                &$skipped,
                &$errors,
                &$processed,
                &$lastTransactionId,
                &$stop,
                $autoPostingService,
                $dryRun,
                $totalTransactions,
                $showProgress,
                $maxSeconds,
                $startedAt
            ) {
                foreach ($transactions as $transaction) {
                    $lastTransactionId = $transaction->id;
                    try {
                        $exists = AccountEntry::where('source_type', 'transaction')
                            ->where('source_id', $transaction->id)
                            ->exists();

                        if ($exists) {
                            $skipped++;
                            $processed++;
                            continue;
                        }

                        $amount = (float) ($transaction->amount + ($transaction->discount_amount ?? 0));
                        if ($amount <= 0) {
                            $skipped++;
                            $processed++;
                            continue;
                        }

                        if ($dryRun) {
                            $created++;
                            $processed++;
                            continue;
                        }

                        $autoPostingService->postTransaction($transaction);
                        $created++;
                    } catch (\Exception $e) {
                        $errors++;
                        $this->error("Transaction {$transaction->id} failed: {$e->getMessage()}");
                    }

                    $processed++;
                }

                if ($showProgress) {
                    $this->line("Adjustments progress: {$processed}/{$totalTransactions} (created {$created}, skipped {$skipped}, errors {$errors})");
                    $this->flushOutput();
                }

                if ($maxSeconds > 0 && (microtime(true) - $startedAt) >= $maxSeconds) {
                    $stop = true;
                    return false;
                }
            });

            if ($stop) {
                $this->emitResume('adjustments', $lastTransactionId, $customerAfter);
                return 2;
            }

            if ($showProgress) {
                $this->newLine();
            }
            $this->info("Adjustments done. Created: {$created}, Skipped: {$skipped}, Errors: {$errors}");

            if (!$runFull) {
                return 0;
            }
        }

        if ($dryRun) {
            $this->warn('Dry run complete. Ledger sync skipped.');
            return 0;
        }

        if ($runSync) {
            $this->info('Re-running ledger sync...');
            $customerQuery = Customer::whereHas('ledgerAccount')->select('id')->orderBy('id');
            if ($customerAfter > 0) {
                $customerQuery->where('id', '>', $customerAfter);
            }
            $totalCustomers = $customerQuery->count();
            $syncErrors = 0;
            $syncProcessed = 0;
            $lastCustomerId = $customerAfter;
            $stop = false;

            $customerQuery->chunkById($chunk, function ($customers) use (
                &$syncErrors,
                &$syncProcessed,
                &$lastCustomerId,
                &$stop,
                $paymentAllocationService,
                $totalCustomers,
                $showProgress,
                $maxSeconds,
                $startedAt
            ) {
                foreach ($customers as $customer) {
                    $lastCustomerId = $customer->id;
                    try {
                        $paymentAllocationService->allocatePayments($customer->id);
                    } catch (\Exception $e) {
                        $syncErrors++;
                        $this->error("Sync failed for customer {$customer->id}: {$e->getMessage()}");
                    }
                    $syncProcessed++;
                }

                if ($showProgress) {
                    $this->line("Sync progress: {$syncProcessed}/{$totalCustomers} (errors {$syncErrors})");
                    $this->flushOutput();
                }

                if ($maxSeconds > 0 && (microtime(true) - $startedAt) >= $maxSeconds) {
                    $stop = true;
                    return false;
                }
            });

            if ($stop) {
                $this->emitResume('sync', $transactionAfter, $lastCustomerId);
                return 2;
            }

            if ($showProgress) {
                $this->newLine();
            }
            $this->info("Ledger sync complete. Errors: {$syncErrors}");
        }

        return 0;
    }

    protected function flushOutput(): void
    {
        if (function_exists('ob_flush')) {
            @ob_flush();
        }
        if (function_exists('flush')) {
            @flush();
        }
    }

    protected function emitResume(string $stage, int $transactionAfter, int $customerAfter): void
    {
        $this->line("RESUME stage={$stage} transaction_after={$transactionAfter} customer_after={$customerAfter}");
        $this->flushOutput();
    }
}
