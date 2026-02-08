<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\PayableTransaction;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Payee;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AutoPostingService
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
    }

    /**
     * Auto-post invoice to GL (Sales entries)
     * Dr: Customer Account (Receivable)
     * Cr: Sales Income
     */
    public function postInvoice(Invoice $invoice): void
    {
        // Get or create customer account
        $customerAccount = $this->getOrCreateCustomerAccount($invoice->customer);

        // Prefer explicitly selected sales account
        $salesAccount = $invoice->sales_account_id
            ? Account::find($invoice->sales_account_id)
            : null;

        // Determine sales account based on invoice type if not provided
        $salesAccountCode = match ($invoice->invoice_type) {
            'tiles' => 'SALES-TILES',
            'sanitary' => 'SALES-SANITARY',
            'other', 'paints' => 'SALES-PAINTS',
            default => 'SALES-TILES',
        };

        if (!$salesAccount) {
            $salesAccount = Account::where('code', $salesAccountCode)->first();
        }
        if (!$salesAccount) {
            // Fallback to first sales account
            $salesAccount = Account::whereHas('accountGroup', fn($q) => $q->where('code', 'SALES'))->first();
        }

        if (!$salesAccount) {
            throw new Exception('Sales income account not configured. Please run ChartOfAccountsSeeder.');
        }

        $entryDate = $invoice->invoice_date?->toDateString() ?? now()->toDateString();

        $entries = [
            $this->makeEntry(
                $customerAccount->id,
                $invoice->total,
                0,
                $invoice->invoice_number,
                "Invoice {$invoice->invoice_number}"
            ),
            $this->makeEntry(
                $salesAccount->id,
                0,
                $invoice->total,
                $invoice->invoice_number,
                "Sales to {$invoice->customer->name}"
            ),
        ];

        $this->persistEntries('invoice', $invoice->id, $entryDate, $entries);
        Log::info("Auto-posted invoice {$invoice->invoice_number} as ledger entries");
    }

    /**
     * Auto-post purchase to GL (Purchase entries)
     * Dr: Purchase Account
     * Cr: Supplier Account (Payable)
     */
    public function postPurchase(Purchase $purchase): void
    {
        // Get or create supplier account
        $supplierAccount = $this->getOrCreateSupplierAccount($purchase->company);

        // Get purchase account
        $purchaseAccount = Account::where('code', 'PURCHASE-TILES')->first();
        if (!$purchaseAccount) {
            $purchaseAccount = Account::whereHas('accountGroup', fn($q) => $q->where('code', 'PURCHASE'))->first();
        }

        if (!$purchaseAccount) {
            throw new Exception('Purchase expense account not configured. Please run ChartOfAccountsSeeder.');
        }

        $totalAmount = $purchase->total_amount ?? $purchase->total ?? 0;
        $entryDate = ($purchase->purchase_date?->toDateString()) ?? ($purchase->created_at?->toDateString() ?? now()->toDateString());
        $reference = $purchase->invoice_no ?? (string) $purchase->id;

        $entries = [
            $this->makeEntry(
                $purchaseAccount->id,
                $totalAmount,
                0,
                $reference,
                "Purchase from {$purchase->company->name}"
            ),
            $this->makeEntry(
                $supplierAccount->id,
                0,
                $totalAmount,
                $reference,
                "Invoice: {$reference}"
            ),
        ];

        $this->persistEntries('purchase', $purchase->id, $entryDate, $entries);
        Log::info("Auto-posted purchase {$purchase->id} as ledger entries");
    }

    /**
     * Auto-post customer payment/receipt
     * Receipt (debit transaction):
     *   Dr: Cash/Bank
     *   Cr: Customer Account
     */
    public function postTransaction(Transaction $transaction): void
    {
        // Invoice credit transactions are already posted via invoice entries
        if ($transaction->type === 'credit' && $transaction->invoice_id && !$transaction->return_id) {
            $this->glService->removeEntries('transaction', $transaction->id);
            return;
        }

        $customerAccount = $this->getOrCreateCustomerAccount($transaction->customer);
        $entryDate = $transaction->created_at?->toDateString() ?? now()->toDateString();
        $reference = $transaction->reference ?? ('TRX-' . $transaction->id);

        if ($transaction->type === 'credit' && $transaction->return_id) {
            $this->postReturnRefund($transaction);
            return;
        }

        if ($transaction->type === 'credit') {
            $amount = (float) ($transaction->amount + ($transaction->discount_amount ?? 0));
            if ($amount <= 0) {
                return;
            }

            $suspenseAccount = $this->getSuspenseAccount();
            $entries = [
                $this->makeEntry(
                    $customerAccount->id,
                    $amount,
                    0,
                    $reference,
                    $transaction->purpose ?? 'Customer ledger adjustment'
                ),
                $this->makeEntry(
                    $suspenseAccount->id,
                    0,
                    $amount,
                    $reference,
                    $transaction->purpose ?? 'Customer ledger adjustment'
                ),
            ];

            $this->persistEntries('transaction', $transaction->id, $entryDate, $entries);
            Log::info("Auto-posted credit transaction {$transaction->id} as ledger entries");
            return;
        }

        if ($transaction->type !== 'debit') {
            return;
        }

        // Return credit note (no cash movement)
        if ($transaction->return_id) {
            $salesAccount = $this->getSalesAccountForInvoice($transaction->invoice);

            $entries = [
                $this->makeEntry(
                    $salesAccount->id,
                    $transaction->amount,
                    0,
                    $reference,
                    $transaction->purpose ?? 'Sales return'
                ),
                $this->makeEntry(
                    $customerAccount->id,
                    0,
                    $transaction->amount,
                    $reference,
                    $transaction->purpose ?? 'Sales return'
                ),
            ];

            $this->persistEntries('transaction', $transaction->id, $entryDate, $entries);
            Log::info("Auto-posted return transaction {$transaction->id} as ledger entries");
            return;
        }

        // Payment received (cash/bank)
        $cashBankAccount = $this->getCashOrBankAccount($transaction->method, $transaction->account_id);

        $entries = [
            $this->makeEntry(
                $cashBankAccount->id,
                $transaction->amount,
                0,
                $reference,
                "Received from {$transaction->customer->name}"
            ),
            $this->makeEntry(
                $customerAccount->id,
                0,
                $transaction->amount,
                $reference,
                $transaction->purpose ?? 'Payment received'
            ),
        ];

        if ($transaction->discount_amount && $transaction->discount_amount > 0) {
            $discountAccount = Account::where('code', 'DISCOUNT-ALLOWED')->first();
            if ($discountAccount) {
                $entries[] = $this->makeEntry(
                    $discountAccount->id,
                    $transaction->discount_amount,
                    0,
                    $reference,
                    $transaction->discount_reason ?? 'Discount allowed'
                );
                $entries[] = $this->makeEntry(
                    $customerAccount->id,
                    0,
                    $transaction->discount_amount,
                    $reference,
                    'Discount on payment'
                );
            }
        }

        $this->persistEntries('transaction', $transaction->id, $entryDate, $entries);
        Log::info("Auto-posted transaction {$transaction->id} as ledger entries");
    }

    /**
     * Get or create customer ledger account
     */
    public function getOrCreateCustomerAccount(Customer $customer): Account
    {
        // Check if account already exists
        $account = Account::where('linkable_type', 'customer')
            ->where('linkable_id', $customer->id)
            ->first();

        if ($account) {
            return $account;
        }

        // Get Sundry Debtors group
        $sundryDebtors = AccountGroup::where('code', 'SUNDRY-DEBTORS')->first();

        if (!$sundryDebtors) {
            throw new Exception('Sundry Debtors account group not found. Please run ChartOfAccountsSeeder.');
        }

        // Create new customer account
        $account = Account::create([
            'name' => $customer->name,
            'code' => 'CUST-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $sundryDebtors->id,
            'account_type' => 'customer',
            'opening_balance' => $customer->opening_balance ?? 0,
            'opening_balance_type' => 'debit',
            'current_balance' => $customer->opening_balance ?? 0,
            'current_balance_type' => 'debit',
            'linkable_type' => 'customer',
            'linkable_id' => $customer->id,
            'is_active' => true,
            'is_system' => false,
        ]);

        Log::info("Created customer account {$account->code} for {$customer->name}");

        return $account;
    }

    /**
     * Get or create supplier ledger account
     */
    public function getOrCreateSupplierAccount(Company $company): Account
    {
        // Check if account already exists
        $account = Account::where('linkable_type', 'company')
            ->where('linkable_id', $company->id)
            ->first();

        if ($account) {
            return $account;
        }

        // Get Sundry Creditors group
        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

        if (!$sundryCreditors) {
            throw new Exception('Sundry Creditors account group not found. Please run ChartOfAccountsSeeder.');
        }

        // Create new supplier account
        $account = Account::create([
            'name' => $company->name,
            'code' => 'SUPP-' . str_pad($company->id, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $sundryCreditors->id,
            'account_type' => 'supplier',
            'opening_balance' => 0,
            'opening_balance_type' => 'credit',
            'current_balance' => 0,
            'current_balance_type' => 'credit',
            'linkable_type' => 'company',
            'linkable_id' => $company->id,
            'is_active' => true,
            'is_system' => false,
        ]);

        Log::info("Created supplier account {$account->code} for {$company->name}");

        return $account;
    }

    /**
     * Get cash or bank account based on payment method
     */
    protected function getCashOrBankAccount(string $method, ?int $accountId = null): Account
    {
        // If a specific account is provided, use it
        if ($accountId) {
            $account = Account::find($accountId);
            if ($account) {
                return $account;
            }
        }

        $code = match ($method) {
            'cash' => 'CASH-PRIMARY',
            'bank', 'bank_transfer' => 'BANK-PRIMARY',
            'mobile_bank' => 'MOBILE-BANK',
            'cheque' => 'BANK-PRIMARY',
            default => 'CASH-PRIMARY',
        };

        $account = Account::where('code', $code)->first();

        if (!$account) {
            // Fallback to any cash account
            $account = Account::where('account_type', 'cash')->first();
        }

        if (!$account) {
            throw new Exception("Cash/Bank account not found ({$code}). Please run ChartOfAccountsSeeder.");
        }

        return $account;
    }

    /**
     * Auto-post customer refund (credit transaction tied to return)
     * Payment ledger entries (cash out):
     *   Dr: Customer Account
     *   Cr: Cash/Bank
     */
    public function postReturnRefund(Transaction $transaction): void
    {
        if ($transaction->type !== 'credit' || !$transaction->return_id) {
            return;
        }

        $customerAccount = $this->getOrCreateCustomerAccount($transaction->customer);
        $cashBankAccount = $this->getCashOrBankAccount($transaction->method, $transaction->account_id);
        $entryDate = $transaction->created_at?->toDateString() ?? now()->toDateString();
        $reference = $transaction->reference ?? ('TRX-' . $transaction->id);

        $entries = [
            $this->makeEntry(
                $customerAccount->id,
                $transaction->amount,
                0,
                $reference,
                $transaction->purpose ?? 'Customer refund'
            ),
            $this->makeEntry(
                $cashBankAccount->id,
                0,
                $transaction->amount,
                $reference,
                $transaction->purpose ?? 'Customer refund'
            ),
        ];

        $this->persistEntries('transaction', $transaction->id, $entryDate, $entries);
        Log::info("Auto-posted return refund transaction {$transaction->id} as ledger entries");
    }

    /**
     * Update existing return refund ledger entries when refund transaction is updated
     */
    public function updateReturnRefundEntries(Transaction $transaction): void
    {
        if ($transaction->type !== 'credit' || !$transaction->return_id) {
            return;
        }

        $this->postReturnRefund($transaction);
    }

    protected function getInterestExpenseAccount(): ?Account
    {
        $account = Account::where('code', 'INTEREST-EXPENSE')->first();
        if ($account) {
            return $account;
        }

        return Account::where('account_type', 'expense')->first();
    }

    protected function getSalesAccountForInvoice(?Invoice $invoice): Account
    {
        $invoiceType = $invoice?->invoice_type ?? 'tiles';
        $salesAccountCode = match ($invoiceType) {
            'tiles' => 'SALES-TILES',
            'sanitary' => 'SALES-SANITARY',
            'other', 'paints' => 'SALES-PAINTS',
            default => 'SALES-TILES',
        };

        $salesAccount = Account::where('code', $salesAccountCode)->first()
            ?? Account::whereHas('accountGroup', fn($q) => $q->where('code', 'SALES'))->first();

        if (!$salesAccount) {
            throw new Exception('Sales income account not configured. Please run ChartOfAccountsSeeder.');
        }

        return $salesAccount;
    }

    protected function getSuspenseAccount(): Account
    {
        $account = Account::where('code', 'SUSPENSE')->first();
        if ($account) {
            return $account;
        }

        $account = Account::where('account_type', 'suspense')->first();
        if ($account) {
            return $account;
        }

        return Account::where('account_type', 'expense')->firstOrFail();
    }

    protected function makeEntry(int $accountId, float $debit, float $credit, ?string $reference, ?string $description): array
    {
        return [
            'account_id' => $accountId,
            'debit_amount' => $debit,
            'credit_amount' => $credit,
            'reference' => $reference,
            'description' => $description,
            'created_by' => auth()->id(),
        ];
    }

    protected function persistEntries(string $sourceType, int $sourceId, string $entryDate, array $entries): void
    {
        $this->glService->replaceEntries($sourceType, $sourceId, $entryDate, $entries);
    }

    /**
     * Update existing invoice ledger entries when invoice is updated
     */
    public function updateInvoiceEntries(Invoice $invoice): void
    {
        $this->postInvoice($invoice);
    }

    /**
     * Update existing transaction ledger entries when transaction is updated
     */
    public function updateTransactionEntries(Transaction $transaction): void
    {
        $this->postTransaction($transaction);
    }

    /**
     * Update existing purchase ledger entries when purchase is updated
     */
    public function updatePurchaseEntries(Purchase $purchase): void
    {
        $this->postPurchase($purchase);
    }

    /**
     * Get all cash and bank accounts for selection
     */
    public static function getCashBankAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::whereIn('account_type', ['cash', 'bank'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all income accounts for selection
     */
    public static function getIncomeAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::whereHas('accountGroup', function ($query) {
            $query->where('nature', 'income');
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all expense accounts for selection
     */
    public static function getExpenseAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::whereHas('accountGroup', function ($query) {
            $query->where('nature', 'expenses');
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Auto-post payable transaction to GL
     * Cash In (Payment to Payee): Dr: Payee Account, Cr: Cash/Bank
     * Cash Out (Received from Payee): Dr: Cash/Bank, Cr: Payee Account
     */
    public function postPayableTransaction(PayableTransaction $transaction): void
    {
        if ($transaction->skip_accounting) {
            return;
        }

        $payeeAccount = $this->getOrCreatePayeeAccount($transaction->payee);
        $entryDate = $transaction->transaction_date?->toDateString() ?? now()->toDateString();
        $reference = $transaction->reference_no ?? ('PTX-' . $transaction->id);

        $entries = [];

        if ($transaction->category === 'colorent_purchase') {
            $purchaseAccount = Account::where('code', 'PURCHASE-TILES')->first();
            if (!$purchaseAccount) {
                $purchaseAccount = Account::whereHas('accountGroup', fn($q) => $q->where('code', 'PURCHASE'))->first();
            }
            if (!$purchaseAccount) {
                $purchaseAccount = Account::whereHas('accountGroup', fn($q) => $q->where('nature', 'expenses'))->first();
            }
            if (!$purchaseAccount) {
                throw new Exception('Purchase expense account not configured. Please run ChartOfAccountsSeeder.');
            }

            $entries[] = $this->makeEntry(
                $purchaseAccount->id,
                $transaction->amount,
                0,
                $reference,
                $transaction->description ?? "Colorent purchase from {$transaction->payee->name}"
            );
            $entries[] = $this->makeEntry(
                $payeeAccount->id,
                0,
                $transaction->amount,
                $reference,
                $transaction->description ?? "Colorent purchase from {$transaction->payee->name}"
            );

            $this->persistEntries('payable_transaction', $transaction->id, $entryDate, $entries);
            Log::info("Auto-posted colorent purchase {$transaction->id} as ledger entries");
            return;
        }

        $cashBankAccount = $this->getCashOrBankAccount($transaction->payment_method ?? 'cash', $transaction->account_id);

        if ($transaction->transaction_type === 'cash_in') {
            $principal = $transaction->principal_amount ?? 0;
            $interest = $transaction->interest_amount ?? 0;
            $hasSplit = ($principal > 0 || $interest > 0);

            if ($hasSplit && !$transaction->payee?->isCcLoan()) {
                $principal = $principal > 0 ? $principal : max(0, $transaction->amount - $interest);

                $entries[] = $this->makeEntry(
                    $payeeAccount->id,
                    $principal,
                    0,
                    $reference,
                    "Principal payment to {$transaction->payee->name}"
                );

                if ($interest > 0) {
                    $interestAccount = $this->getInterestExpenseAccount();
                    if ($interestAccount) {
                        $entries[] = $this->makeEntry(
                            $interestAccount->id,
                            $interest,
                            0,
                            $reference,
                            'Interest expense'
                        );
                    }
                }

                $entries[] = $this->makeEntry(
                    $cashBankAccount->id,
                    0,
                    $transaction->amount,
                    $reference,
                    $transaction->description ?? "Payment for {$transaction->category}"
                );
            } else {
                $entries[] = $this->makeEntry(
                    $payeeAccount->id,
                    $transaction->amount,
                    0,
                    $reference,
                    "Payment to {$transaction->payee->name}"
                );
                $entries[] = $this->makeEntry(
                    $cashBankAccount->id,
                    0,
                    $transaction->amount,
                    $reference,
                    $transaction->description ?? "Payment for {$transaction->category}"
                );
            }
        } else {
            $entries[] = $this->makeEntry(
                $cashBankAccount->id,
                $transaction->amount,
                0,
                $reference,
                "Received from {$transaction->payee->name}"
            );
            $entries[] = $this->makeEntry(
                $payeeAccount->id,
                0,
                $transaction->amount,
                $reference,
                $transaction->description ?? "Received for {$transaction->category}"
            );
        }

        $this->persistEntries('payable_transaction', $transaction->id, $entryDate, $entries);
        Log::info("Auto-posted payable transaction {$transaction->id} as ledger entries");
    }

    /**
     * Get or create payee account in Sundry Creditors
     */
    public function getOrCreatePayeeAccount(Payee $payee): Account
    {
        if ($payee->account_id) {
            $linked = Account::find($payee->account_id);
            if ($linked) {
                return $linked;
            }
        }

        // Check if account already exists
        $account = Account::where('linkable_type', 'payee')
            ->where('linkable_id', $payee->id)
            ->first();

        if ($account) {
            return $account;
        }

        // Get Sundry Creditors group
        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();
        if (!$sundryCreditors) {
            throw new Exception('Sundry Creditors account group not found. Please run ChartOfAccountsSeeder.');
        }

        $accountType = $payee->category === 'supplier' ? 'supplier' : 'liability';

        // Create new payee account
        $account = Account::create([
            'name' => $payee->name,
            'code' => 'PAY-' . str_pad($payee->id, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $sundryCreditors->id,
            'account_type' => $accountType,
            'opening_balance' => $payee->opening_balance ?? 0,
            'opening_balance_type' => 'credit',
            'current_balance' => $payee->opening_balance ?? 0,
            'current_balance_type' => 'credit',
            'linkable_type' => 'payee',
            'linkable_id' => $payee->id,
            'is_active' => true,
            'is_system' => false,
            'notes' => "Auto-created for payee: {$payee->name}",
        ]);

        Log::info("Created ledger account for payee: {$payee->name}");

        return $account;
    }

    /**
     * Generate narration for payable transaction
     */
    protected function getPayableNarration(PayableTransaction $transaction): string
    {
        $type = $transaction->transaction_type === 'cash_out' ? 'Payment to' : 'Refund from';
        $narration = "{$type} {$transaction->payee->name}";

        if ($transaction->category) {
            $narration .= " for {$transaction->category}";
        }

        if ($transaction->description) {
            $narration .= " - {$transaction->description}";
        }

        return $narration;
    }
}
