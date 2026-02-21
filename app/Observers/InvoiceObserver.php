<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\GeneralLedgerService;
use App\Services\MobileNotificationService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    protected AutoPostingService $autoPostingService;
    protected GeneralLedgerService $glService;
    protected MobileNotificationService $mobileNotificationService;

    public function __construct(
        AutoPostingService $autoPostingService,
        GeneralLedgerService $glService,
        MobileNotificationService $mobileNotificationService
    ) {
        $this->autoPostingService = $autoPostingService;
        $this->glService = $glService;
        $this->mobileNotificationService = $mobileNotificationService;
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

        try {
            $invoice->loadMissing('customer:id,name');
            $this->mobileNotificationService->notifyEvent(
                'invoice_created',
                'New Invoice',
                'Invoice ' . $invoice->invoice_number . ' for ' . number_format((float) $invoice->total, 2) . ' created.',
                [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer?->name,
                    'amount' => (float) $invoice->total,
                ],
                $invoice->tenant_id ? (int) $invoice->tenant_id : null
            );
        } catch (\Exception $e) {
            Log::warning('Failed to push mobile invoice notification', [
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

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        try {
            $this->autoPostingService->postInvoice($invoice);
        } catch (\Exception $e) {
            Log::error('Failed to restore invoice ledger entries', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
