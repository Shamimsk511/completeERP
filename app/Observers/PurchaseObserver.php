<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Support\Facades\Log;

class PurchaseObserver
{
    protected AutoPostingService $autoPostingService;
    protected GeneralLedgerService $glService;

    public function __construct(AutoPostingService $autoPostingService, GeneralLedgerService $glService)
    {
        $this->autoPostingService = $autoPostingService;
        $this->glService = $glService;
    }

    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        try {
            $this->autoPostingService->postPurchase($purchase);
        } catch (\Exception $e) {
            Log::error('Failed to auto-post purchase ledger entries', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase): void
    {
        // Only update ledger entries if the total amount changed
        if ($purchase->isDirty('total_amount') || $purchase->isDirty('total') || $purchase->isDirty('grand_total')) {
            try {
                $this->autoPostingService->updatePurchaseEntries($purchase);
            } catch (\Exception $e) {
                Log::error('Failed to update purchase ledger entries', [
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Purchase "deleted" event.
     */
    public function deleted(Purchase $purchase): void
    {
        try {
            $this->glService->removeEntries('purchase', $purchase->id);
        } catch (\Exception $e) {
            Log::error('Failed to remove purchase ledger entries', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
