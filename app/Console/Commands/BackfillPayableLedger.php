<?php

namespace App\Console\Commands;

use App\Models\PayableTransaction;
use App\Models\Tenant;
use App\Models\Accounting\AccountEntry;
use App\Services\Accounting\AutoPostingService;
use App\Support\TenantContext;
use Illuminate\Console\Command;

class BackfillPayableLedger extends Command
{
    protected $signature = 'ledger:backfill-payables
        {--tenant= : Tenant ID to process (default: all tenants)}
        {--from= : Start date (YYYY-MM-DD) filter by transaction_date}
        {--to= : End date (YYYY-MM-DD) filter by transaction_date}
        {--chunk=200 : Chunk size}
        {--dry-run : Show what would be done without changes}
        {--no-progress : Suppress progress output}
        {--max-seconds= : Stop after N seconds and output resume info}
        {--after= : Resume after payable transaction ID}
        {--force : Rebuild even if ledger entries exist}';

    protected $description = 'Backfill ledger entries from payable transactions (payables source of truth).';

    public function handle(AutoPostingService $autoPostingService): int
    {
        $originalTenantId = TenantContext::currentId();
        $tenantId = $this->option('tenant');
        $from = $this->option('from');
        $to = $this->option('to');
        $chunk = (int) ($this->option('chunk') ?: 200);
        $dryRun = (bool) $this->option('dry-run');
        $showProgress = !$this->option('no-progress');
        $maxSeconds = (int) ($this->option('max-seconds') ?: 0);
        $after = (int) ($this->option('after') ?: 0);
        $force = (bool) $this->option('force');
        $startedAt = microtime(true);

        if ($dryRun) {
            $this->warn('DRY RUN - no changes will be saved.');
        }

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

            $query = PayableTransaction::query()->orderBy('id');
            if ($from) {
                $query->whereDate('transaction_date', '>=', $from);
            }
            if ($to) {
                $query->whereDate('transaction_date', '<=', $to);
            }
            if ($after > 0) {
                $query->where('id', '>', $after);
            }

            $total = $query->count();
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors = 0;
            $processed = 0;
            $lastId = $after;
            $stop = false;

            $query->chunkById($chunk, function ($transactions) use (
                &$created,
                &$updated,
                &$skipped,
                &$errors,
                &$processed,
                &$lastId,
                &$stop,
                $autoPostingService,
                $dryRun,
                $force,
                $total,
                $showProgress,
                $maxSeconds,
                $startedAt
            ) {
                foreach ($transactions as $transaction) {
                    $lastId = $transaction->id;
                    try {
                        $exists = AccountEntry::where('source_type', 'payable_transaction')
                            ->where('source_id', $transaction->id)
                            ->exists();

                        if ($exists && !$force) {
                            $skipped++;
                        } else {
                            if ($dryRun) {
                                if ($exists) {
                                    $updated++;
                                } else {
                                    $created++;
                                }
                            } else {
                                $autoPostingService->postPayableTransaction($transaction);
                                if ($exists) {
                                    $updated++;
                                } else {
                                    $created++;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $errors++;
                        $this->error("Payable transaction {$transaction->id} failed: {$e->getMessage()}");
                    }

                    $processed++;
                }

                if ($showProgress) {
                    $this->line("Progress: {$processed}/{$total} (created {$created}, updated {$updated}, skipped {$skipped}, errors {$errors})");
                    $this->flushOutput();
                }

                if ($maxSeconds > 0 && (microtime(true) - $startedAt) >= $maxSeconds) {
                    $stop = true;
                    return false;
                }
            });

            if ($stop) {
                $this->emitResume($tenant->id, $lastId);
                $this->restoreTenant($originalTenantId);
                return 2;
            }

            if ($showProgress) {
                $this->newLine();
            }
            $this->info("Tenant {$tenant->id} done. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}");
        }

        $this->restoreTenant($originalTenantId);
        return self::SUCCESS;
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

    protected function emitResume(int $tenantId, int $afterId): void
    {
        $this->line("RESUME tenant_id={$tenantId} after={$afterId}");
        $this->flushOutput();
    }

    protected function restoreTenant(?int $tenantId): void
    {
        if ($tenantId) {
            TenantContext::set($tenantId);
        } else {
            TenantContext::clear();
        }
    }
}
