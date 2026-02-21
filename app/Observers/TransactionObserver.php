<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\GeneralLedgerService;
use App\Services\MobileNotificationService;
use Illuminate\Support\Facades\Log;

class TransactionObserver
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

        try {
            $transaction->loadMissing(['customer:id,name', 'invoice:id,invoice_number']);

            $isPayment = $transaction->type === 'debit';
            $title = $isPayment ? 'New Payment' : 'New Transaction';
            $message = $isPayment
                ? 'Payment of ' . number_format((float) $transaction->amount, 2) . ' received.'
                : 'Transaction of ' . number_format((float) $transaction->amount, 2) . ' recorded.';

            $this->mobileNotificationService->notifyEvent(
                $isPayment ? 'payment_created' : 'transaction_created',
                $title,
                $message,
                [
                    'transaction_id' => $transaction->id,
                    'customer_name' => $transaction->customer?->name,
                    'invoice_number' => $transaction->invoice?->invoice_number,
                    'amount' => (float) $transaction->amount,
                    'type' => $transaction->type,
                ],
                $transaction->tenant_id ? (int) $transaction->tenant_id : null
            );
        } catch (\Exception $e) {
            Log::warning('Failed to push mobile transaction notification', [
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
