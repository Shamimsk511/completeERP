<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    protected AutoPostingService $autoPostingService;
    protected GeneralLedgerService $glService;

    public function __construct(AutoPostingService $autoPostingService, GeneralLedgerService $glService)
    {
        $this->autoPostingService = $autoPostingService;
        $this->glService = $glService;
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        try {
            $this->autoPostingService->postInvoice($invoice);
        } catch (\Exception $e) {
            Log::error('Failed to auto-post invoice ledger entries', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->isDirty(['total', 'grand_total', 'sales_account_id', 'invoice_type'])) {
            try {
                $this->autoPostingService->updateInvoiceEntries($invoice);
            } catch (\Exception $e) {
                Log::error('Failed to update invoice ledger entries', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        try {
            $this->glService->removeEntries('invoice', $invoice->id);
        } catch (\Exception $e) {
            Log::error('Failed to remove invoice ledger entries', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
