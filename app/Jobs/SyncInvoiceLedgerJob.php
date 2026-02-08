<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\PaymentAllocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncInvoiceLedgerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public function __construct(public string $syncId)
    {
    }

    public function handle(PaymentAllocationService $paymentAllocationService): void
    {
        $cacheKey = "ledger_sync:{$this->syncId}";
        $state = [];
        $startedAt = null;

        try {
            $state = Cache::get($cacheKey, []);
            $currentStatus = strtolower((string) ($state['status'] ?? ''));
            if (in_array($currentStatus, ['running-inline', 'completed'], true)) {
                return;
            }

            $startedAt = $state['started_at'] ?? now()->toDateTimeString();
            $total = (int) ($state['total'] ?? Customer::whereHas('ledgerAccount')->count());
            $processed = 0;
            $errors = 0;

            Cache::put($cacheKey, array_merge($state, [
                'status' => 'running',
                'total' => $total,
                'processed' => 0,
                'errors' => 0,
                'started_at' => $startedAt,
                'finished_at' => null,
            ]), now()->addHours(6));

            Customer::whereHas('ledgerAccount')
                ->select('id')
                ->chunkById(200, function ($customers) use (&$processed, &$errors, $paymentAllocationService, $cacheKey, $total, $startedAt) {
                    foreach ($customers as $customer) {
                        try {
                            $paymentAllocationService->allocatePayments($customer->id);
                        } catch (\Exception $e) {
                            $errors++;
                            Log::error("Ledger sync failed for customer {$customer->id}: " . $e->getMessage());
                        }
                        $processed++;
                    }

                    Cache::put($cacheKey, [
                        'status' => 'running',
                        'total' => $total,
                        'processed' => $processed,
                        'errors' => $errors,
                        'started_at' => $startedAt,
                        'finished_at' => null,
                    ], now()->addHours(6));
                });

            Cache::put($cacheKey, [
                'status' => 'completed',
                'total' => $total,
                'processed' => $processed,
                'errors' => $errors,
                'started_at' => $startedAt,
                'finished_at' => now()->toDateTimeString(),
            ], now()->addHours(6));
        } catch (\Exception $e) {
            Log::error("Ledger sync job failed: " . $e->getMessage());
            Cache::put($cacheKey, [
                'status' => 'failed',
                'total' => $state['total'] ?? 0,
                'processed' => $state['processed'] ?? 0,
                'errors' => $state['errors'] ?? 0,
                'started_at' => $startedAt ?? now()->toDateTimeString(),
                'finished_at' => now()->toDateTimeString(),
                'message' => $e->getMessage(),
            ], now()->addHours(6));

            throw $e;
        }
    }
}
