# ERP26 Agent Reference

**Overview**
This repository is a Laravel 12 ERP-style web app for sales, inventory, customers, accounting, and operations. It includes AdminLTE-based admin UI, SMS notifications, and an internal accounting ledger. The source of truth for customer balances is the `transactions` table, not accounting vouchers.

**Main Domains**
1. Customers and CRM.
2. Sales: invoices, challans (deliveries), returns.
3. Inventory: products, categories, companies, godowns.
4. Finance: customer transactions, payables, cash flow, aging.
5. Accounting: chart of accounts, vouchers, ledger, auto-posting.
6. HR/Payroll and tools (decor calculator, etc.).

**Core Entities**
1. Customer: `opening_balance`, `outstanding_balance`, optional account group, ledger account.
2. Invoice: line items, totals, `paid_amount`, `due_amount`, `payment_status`, `previous_balance`, `initial_paid_amount`.
3. Transaction: customer-linked entries with `type` (`credit` or `debit`), `amount`, `discount_amount`, optional `invoice_id`/`return_id`, `account_id`.
4. ProductReturn: return header + items, linked to customer and optional invoice.
5. Accounting: `Account`, `AccountGroup`, `Voucher`, `VoucherEntry` (double-entry).

**Customer Balance Rules (Source of Truth)**
1. Customer balance is derived only from `transactions`.
2. Formula: `opening_balance + total_credits - total_debits`.
3. Debits include discounts: `amount + discount_amount`.
4. Credits represent charges (invoice totals, extra expenses, refunds paid to customer).
5. Debits represent payments, discounts, and returns (reduce balance).
6. On customer create: `outstanding_balance = opening_balance` (or `0` when none).

**Invoice Allocation Logic**
Implemented in `app/Services/PaymentAllocationService.php`.
1. Reset invoice payment fields in memory.
2. Apply invoice-specific debits first (payments linked to that invoice).
3. Allocate remaining debits FIFO to oldest invoices.
4. `paid_amount` includes discounts; `due_amount = total - paid_amount`.
5. Status: `paid`, `partial`, or `due`.
6. Always call allocation after any invoice, transaction, or return changes.

**Invoice Immutability (Historical Snapshot)**
1. `previous_balance` stores customer balance at invoice creation.
2. `initial_paid_amount` stores payment made at invoice creation.
3. Use these fields in invoice show/print for historical display; do not recompute past invoices.

**Customer Balance Summary**
Table: `customer_balance_summary`.
1. Updated by `PaymentAllocationService`.
2. Columns: total invoices amount/count, total payments amount, calculated balance, paid/pending counts.
3. `total_payments_amount` includes discounts.

**Returns and Refunds**
1. Return creates a debit transaction that reduces outstanding balance.
2. If return exceeds outstanding, optional refund creates a credit transaction (cash out).
3. Refund amount is limited to `return_total - outstanding_before`.
4. Refund requires a cash/bank account; voucher auto-posted as a payment voucher.
5. Return debit can link to `invoice_id` when applicable to reduce that invoice.

**Accounting and Vouchers**
1. Vouchers are for internal accounting only; they do not change customer balances.
2. Auto-posting mappings: Invoice -> Sales voucher (Dr Customer, Cr Sales). Customer payment -> Receipt voucher (Dr Cash/Bank, Cr Customer). Return refund -> Payment voucher (Dr Customer, Cr Cash/Bank). Purchases and payables have dedicated postings.
3. `GeneralLedgerService` posts vouchers and updates account balances only.

**Observers and Automation**
1. `TransactionObserver` auto-posts or updates vouchers for debit transactions (customer payments) and credit transactions linked to returns (refunds).
2. Voucher updates trigger on changes to amount, discount, method, or account.

**Key Files**
1. `app/Services/PaymentAllocationService.php`
2. `app/Services/Accounting/AutoPostingService.php`
3. `app/Services/Accounting/GeneralLedgerService.php`
4. `app/Observers/TransactionObserver.php`
5. `app/Http/Controllers/InvoiceController.php`
6. `app/Http/Controllers/TransactionController.php`
7. `app/Http/Controllers/ProductReturnController.php`
8. `resources/views/returns/create.blade.php`
9. `resources/views/returns/edit.blade.php`
10. `resources/views/invoices/show.blade.php`

**Operational Guardrails**
1. Do not manually update `customers.outstanding_balance` without reallocation.
2. Do not use vouchers to sync customer balances.
3. Always include discounts in debit totals and paid amounts.
4. Keep invoice status derived from allocation; do not hand-edit `paid_amount` or `due_amount`.
