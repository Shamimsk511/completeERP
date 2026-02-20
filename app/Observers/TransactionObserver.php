<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    protected AutoPostingService $autoPostingService;
    protected GeneralLedgerService $glService;

    public function __construct(AutoPostingService $autoPostingService, GeneralLedgerService $glService)
    {
        $this->autoPostingService = $autoPostingService;
        $this->glService = $glService;
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        try {
            if ($transaction->type === 'debit') {
                $this->autoPostingService->postTransaction($transaction);
            } elseif ($transaction->type === 'credit' && $transaction->return_id) {
                $this->autoPostingService->postReturnRefund($transaction);
            } elseif ($transaction->type === 'credit') {
                $this->autoPostingService->postTransaction($transaction);
            }
        } catch (\Exception $e) {
            Log::error('Failed to auto-post transaction ledger entries', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if ($transaction->isDirty(['amount', 'discount_amount', 'method', 'account_id'])) {
            try {
                if ($transaction->type === 'debit') {
                    $this->autoPostingService->updateTransactionEntries($transaction);
                } elseif ($transaction->type === 'credit' && $transaction->return_id) {
                    $this->autoPostingService->updateReturnRefundEntries($transaction);
                } elseif ($transaction->type === 'credit') {
                    $this->autoPostingService->updateTransactionEntries($transaction);
                }
            } catch (\Exception $e) {
                    Log::error('Failed to update transaction ledger entries', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        try {
            $this->glService->removeEntries('transaction', $transaction->id);
        } catch (\Exception $e) {
            Log::error('Failed to remove transaction ledger entries', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        try {
            if ($transaction->type === 'debit') {
                $this->autoPostingService->postTransaction($transaction);
            } elseif ($transaction->type === 'credit' && $transaction->return_id) {
                $this->autoPostingService->postReturnRefund($transaction);
            } elseif ($transaction->type === 'credit') {
                $this->autoPostingService->postTransaction($transaction);
            }
        } catch (\Exception $e) {
            Log::error('Failed to restore transaction ledger entries', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
