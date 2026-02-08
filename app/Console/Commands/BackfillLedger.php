<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Accounting\AccountEntry;
use App\Services\Accounting\AutoPostingService;
use App\Services\PaymentAllocationService;
use Illuminate\Console\Command;

class BackfillLedger extends Command
{
    protected $signature = 'ledger:backfill {--from=} {--to=} {--chunk=200} {--dry-run} {--no-progress} {--max-seconds=} {--stage=} {--invoice-after=} {--payment-after=} {--customer-after=}';

    protected $description = 'Backfill ledger entries from invoices and customer payments, then sync ledger balances.';

    public function handle(AutoPostingService $autoPostingService, PaymentAllocationService $paymentAllocationService): int
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $chunk = (int) ($this->option('chunk') ?: 200);
        $dryRun = (bool) $this->option('dry-run');
        $showProgress = !$this->option('no-progress');
        $maxSeconds = (int) ($this->option('max-seconds') ?: 0);
        $stage = $this->option('stage');
        $stage = is_string($stage) ? strtolower(trim($stage)) : null;
        $invoiceAfter = (int) ($this->option('invoice-after') ?: 0);
        $paymentAfter = (int) ($this->option('payment-after') ?: 0);
        $customerAfter = (int) ($this->option('customer-after') ?: 0);
        $startedAt = microtime(true);
        $runFull = !$stage;
        $runInvoices = $runFull || $stage === 'invoices';
        $runPayments = $runFull || $stage === 'payments';
        $runSync = $runFull || $stage === 'sync';

        if ($dryRun) {
            $this->warn('DRY RUN - no changes will be saved.');
        }

        if ($runInvoices) {
            $this->info('Backfilling invoice ledger entries...');
            $invoiceQuery = Invoice::query()->orderBy('id');
            if ($from) {
                $invoiceQuery->whereDate('invoice_date', '>=', $from);
            }
            if ($to) {
                $invoiceQuery->whereDate('invoice_date', '<=', $to);
            }
            if ($invoiceAfter > 0) {
                $invoiceQuery->where('id', '>', $invoiceAfter);
            }

            $totalInvoices = $invoiceQuery->count();
            $invoiceCreated = 0;
            $invoiceSkipped = 0;
            $invoiceErrors = 0;
            $invoiceProcessed = 0;
            $lastInvoiceId = $invoiceAfter;
            $stop = false;

            $invoiceQuery->chunkById($chunk, function ($invoices) use (
                &$invoiceCreated,
                &$invoiceSkipped,
                &$invoiceErrors,
                &$invoiceProcessed,
                &$lastInvoiceId,
                &$stop,
                $autoPostingService,
                $dryRun,
                $totalInvoices,
                $showProgress,
                $maxSeconds,
                $startedAt
            ) {
                foreach ($invoices as $invoice) {
                    $lastInvoiceId = $invoice->id;
                    try {
                        $exists = AccountEntry::where('source_type', 'invoice')
                            ->where('source_id', $invoice->id)
                            ->exists();

                        if ($exists) {
                            $invoiceSkipped++;
                        } else {
                            if ($dryRun) {
                                $invoiceCreated++;
                            } else {
                                $autoPostingService->postInvoice($invoice);
                                $invoiceCreated++;
                            }
                        }
                    } catch (\Exception $e) {
                        $invoiceErrors++;
                        $this->error("Invoice {$invoice->id} failed: {$e->getMessage()}");
                    }

                    $invoiceProcessed++;
                }

                if ($showProgress) {
                    $this->line("Invoices progress: {$invoiceProcessed}/{$totalInvoices} (created {$invoiceCreated}, skipped {$invoiceSkipped}, errors {$invoiceErrors})");
                    $this->flushOutput();
                }

                if ($maxSeconds > 0 && (microtime(true) - $startedAt) >= $maxSeconds) {
                    $stop = true;
                    return false;
                }
            });

            if ($stop) {
                $this->emitResume('invoices', $lastInvoiceId, $paymentAfter, $customerAfter);
                return 2;
            }

            if ($showProgress) {
                $this->newLine();
            }
            $this->info("Invoices done. Created: {$invoiceCreated}, Skipped: {$invoiceSkipped}, Errors: {$invoiceErrors}");

            if (!$runFull) {
                return 0;
            }
        }

        if ($runPayments) {
            $this->info('Backfilling payment ledger entries...');
            $paymentQuery = Transaction::query()
                ->where('type', 'debit')
                ->orderBy('id');

            if ($from) {
                $paymentQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $paymentQuery->whereDate('created_at', '<=', $to);
            }
            if ($paymentAfter > 0) {
                $paymentQuery->where('id', '>', $paymentAfter);
            }

            $totalPayments = $paymentQuery->count();
            $paymentCreated = 0;
            $paymentSkipped = 0;
            $paymentErrors = 0;
            $paymentProcessed = 0;
            $lastPaymentId = $paymentAfter;
            $stop = false;

            $paymentQuery->chunkById($chunk, function ($transactions) use (
                &$paymentCreated,
                &$paymentSkipped,
                &$paymentErrors,
                &$paymentProcessed,
                &$lastPaymentId,
                &$stop,
                $autoPostingService,
                $dryRun,
                $totalPayments,
                $showProgress,
                $maxSeconds,
                $startedAt
            ) {
                foreach ($transactions as $transaction) {
                    $lastPaymentId = $transaction->id;
                    try {
                        $exists = AccountEntry::where('source_type', 'transaction')
                            ->where('source_id', $transaction->id)
                            ->exists();

                        if ($exists) {
                            $paymentSkipped++;
                        } else {
                            if ($dryRun) {
                                $paymentCreated++;
                            } else {
                                $autoPostingService->postTransaction($transaction);
                                $paymentCreated++;
                            }
                        }
                    } catch (\Exception $e) {
                        $paymentErrors++;
                        $this->error("Transaction {$transaction->id} failed: {$e->getMessage()}");
                    }

                    $paymentProcessed++;
                }

                if ($showProgress) {
                    $this->line("Payments progress: {$paymentProcessed}/{$totalPayments} (created {$paymentCreated}, skipped {$paymentSkipped}, errors {$paymentErrors})");
                    $this->flushOutput();
                }

                if ($maxSeconds > 0 && (microtime(true) - $startedAt) >= $maxSeconds) {
                    $stop = true;
                    return false;
                }
            });

            if ($stop) {
                $this->emitResume('payments', $invoiceAfter, $lastPaymentId, $customerAfter);
                return 2;
            }

            if ($showProgress) {
                $this->newLine();
            }
            $this->info("Payments done. Created: {$paymentCreated}, Skipped: {$paymentSkipped}, Errors: {$paymentErrors}");

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
                $this->emitResume('sync', $invoiceAfter, $paymentAfter, $lastCustomerId);
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

    protected function emitResume(string $stage, int $invoiceAfter, int $paymentAfter, int $customerAfter): void
    {
        $this->line("RESUME stage={$stage} invoice_after={$invoiceAfter} payment_after={$paymentAfter} customer_after={$customerAfter}");
        $this->flushOutput();
    }
}
