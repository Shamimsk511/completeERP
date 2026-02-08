<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentAllocationService
{
    /**
     * Handle invoice deletion and update customer balance accordingly
     * 
     * @param Invoice $invoice
     * @return void
     */
    public function handleInvoiceDeletion(Invoice $invoice)
    {
        //Log::info("Handling invoice deletion for invoice ID: {$invoice->id}, customer ID: {$invoice->customer_id}");
        
        try {
            DB::beginTransaction();
            
            $customer = Customer::findOrFail($invoice->customer_id);
            
            // Calculate the impact of removing this invoice
            $invoiceTotal = $invoice->total;
            $invoicePayments = Transaction::where('customer_id', $invoice->customer_id)
                ->where('type', 'debit')
                ->where('invoice_id', $invoice->id)
                ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
                ->value('total');
            
            // Update customer balance: subtract invoice amount, add back payments
            $balanceAdjustment = -$invoiceTotal + $invoicePayments;
            $customer->outstanding_balance += $balanceAdjustment;
            $customer->save();
            
           // Log::info("Customer {$customer->id} balance adjusted by {$balanceAdjustment} after invoice deletion");
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error handling invoice deletion: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Allocate payments to invoices for a specific customer
     * Payments will be allocated to invoices with linked transactions first, then oldest invoices
     * 
     * @param int $customerId
     * @return void
     */
    public function allocatePayments($customerId)
    {
        // Customer balances must be derived from transactions only.
        $this->allocatePaymentsFromTransactions($customerId);
    }

    /**
     * Legacy allocation based on transactions table (pre-accounting ledger)
     *
     * @param int $customerId
     * @return void
     */
    protected function allocatePaymentsFromTransactions($customerId): void
    {
        try {
            DB::beginTransaction();
            
            // Get customer
            $customer = Customer::findOrFail($customerId);
            
            // Get invoices with linked debit transactions
            $invoicesWithPayments = Invoice::where('customer_id', $customerId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('transactions')
                          ->whereColumn('transactions.invoice_id', 'invoices.id')
                          ->where('transactions.type', 'debit');
                })
                ->orderBy('invoice_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            
            // Get all other invoices ordered by date (oldest first)
            $otherInvoices = Invoice::where('customer_id', $customerId)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('transactions')
                          ->whereColumn('transactions.invoice_id', 'invoices.id')
                          ->where('transactions.type', 'debit');
                })
                ->orderBy('invoice_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            
            // Combine invoices (prioritize those with payments)
            $invoices = $invoicesWithPayments->concat($otherInvoices);
            
            // Get total payment amount (debits) including discounts
            $totalPayments = (float) Transaction::where('customer_id', $customerId)
                ->where('type', 'debit')
                ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
                ->value('total');
            
            // Get total charges (credits)
            $totalCharges = (float) Transaction::where('customer_id', $customerId)
                ->where('type', 'credit')
                ->sum('amount');

            // Calculate customer's actual balance
            $customerBalance = ($customer->opening_balance ?? 0) + $totalCharges - $totalPayments;
            
            // Reset all invoice payments in-memory before allocation
            foreach ($invoices as $invoice) {
                $invoice->paid_amount = 0;
                $invoice->due_amount = (float) $invoice->total;
                $invoice->payment_status = 'due';
            }
            
            $remainingPayment = $totalPayments;
            
            // Allocate payments to invoices
            foreach ($invoices as $invoice) {
                if ($remainingPayment <= 0) {
                    break;
                }

                // Get payments specifically for this invoice
                $invoicePayments = (float) Transaction::where('customer_id', $customerId)
                    ->where('type', 'debit')
                    ->where('invoice_id', $invoice->id)
                    ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
                    ->value('total');

                $invoiceTotal = (float) $invoice->total; // After-discount total
                $paidForInvoice = min($invoicePayments, $invoiceTotal);

                // Update invoice
                $invoice->paid_amount = $paidForInvoice;
                $invoice->due_amount = $invoiceTotal - $paidForInvoice;

                // Set payment status
                if ($invoice->due_amount <= 0) {
                    $invoice->payment_status = 'paid';
                } elseif ($paidForInvoice > 0) {
                    $invoice->payment_status = 'partial';
                } else {
                    $invoice->payment_status = 'due';
                }

                $remainingPayment -= $paidForInvoice;
                if ($remainingPayment < 0) {
                    $remainingPayment = 0;
                }
            }

            // Allocate remaining payments FIFO to oldest due invoices
            if ($remainingPayment > 0) {
                foreach ($invoices as $invoice) {
                    if ($remainingPayment <= 0) {
                        break;
                    }

                    if ($invoice->due_amount <= 0) {
                        continue;
                    }

                    $paid = min($remainingPayment, (float) $invoice->due_amount);
                    $invoice->paid_amount += $paid;
                    $invoice->due_amount -= $paid;
                    $invoice->payment_status = $invoice->due_amount <= 0 ? 'paid' : 'partial';

                    $remainingPayment -= $paid;
                }
            }

            foreach ($invoices as $invoice) {
                $invoice->save();
            }
            
            // Update customer's outstanding balance
            $customer->outstanding_balance = $customerBalance;
            $customer->save();

            $this->updateCustomerBalanceSummary($customer, $totalCharges, $totalPayments, $customerBalance);
            
            //Log::info("Customer {$customerId} balance updated to: {$customerBalance}");
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment allocation error for customer {$customerId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Allocate payments based on posted ledger entries (ledger source of truth)
     *
     * @param int $customerId
     * @return void
     */
    public function allocatePaymentsFromLedger($customerId): void
    {
        $this->allocatePaymentsFromTransactions($customerId);
    }
    
    /**
     * Get customer's total due amount after all allocations
     * 
     * @param int $customerId
     * @return float
     */
public function getCustomerTotalDue($customerId)
{
    $customer = Customer::findOrFail($customerId);
    $totalInvoiceAmount = Invoice::where('customer_id', $customerId)->sum('total');
    $totalPayments = (float) Transaction::where('customer_id', $customerId)
        ->where('type', 'debit')
        ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
        ->value('total');
    
    // Include opening balance in the calculation
    $openingBalance = $customer->opening_balance ?? 0;
    
    return max(0, $openingBalance + $totalInvoiceAmount - $totalPayments);
}
    
    /**
     * Calculate how much payment is needed to fully pay an invoice
     * 
     * @param Invoice $invoice
     * @return float
     */
    public function getInvoiceRemainingDue($invoice)
    {
        // Trigger reallocation
        $this->allocatePayments($invoice->customer_id);
        
        // Refresh invoice
        $invoice->refresh();
        
        return $invoice->due_amount;
    }

    protected function updateCustomerBalanceSummary(Customer $customer, float $totalCharges, float $totalPayments, float $calculatedBalance): void
    {
        $invoiceStats = Invoice::where('customer_id', $customer->id)
            ->selectRaw('
                COUNT(*) as total_invoices_count,
                COALESCE(SUM(total), 0) as total_invoices_amount,
                SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_invoices_count,
                SUM(CASE WHEN payment_status != "paid" THEN 1 ELSE 0 END) as pending_invoices_count
            ')
            ->first();

        $tenantId = $customer->tenant_id;

        DB::table('customer_balance_summary')->updateOrInsert(
            ['customer_id' => $customer->id, 'tenant_id' => $tenantId],
            [
                'tenant_id' => $tenantId,
                'total_invoices_amount' => (float) ($invoiceStats->total_invoices_amount ?? 0),
                'total_payments_amount' => $totalPayments,
                'calculated_balance' => $calculatedBalance,
                'total_invoices_count' => (int) ($invoiceStats->total_invoices_count ?? 0),
                'paid_invoices_count' => (int) ($invoiceStats->paid_invoices_count ?? 0),
                'pending_invoices_count' => (int) ($invoiceStats->pending_invoices_count ?? 0),
                'last_updated' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('customer_balance_summary')
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $tenantId)
            ->whereNull('created_at')
            ->update(['created_at' => now()]);
    }
}
