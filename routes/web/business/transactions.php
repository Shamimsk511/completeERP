<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

Route::resource('transactions', TransactionController::class);

// Customer ledger routes
Route::get('customers/{customer}/ledger', [TransactionController::class, 'customerLedger'])->name('customers.ledger');
Route::get('/customers/{customer}/ledger/print', [TransactionController::class, 'printCustomerLedger'])->name('customers.ledger.print');

// Transaction printing
Route::get('/transactions/{transaction}/print', function (App\Models\Transaction $transaction) {
    return view('transactions/transaction-print', compact('transaction'));
})->name('transactions.print');

// Customer search and invoice management
Route::get('customers/search', [TransactionController::class, 'searchCustomers'])->name('customers.search');
Route::get('customers/{customer}/invoices', [TransactionController::class, 'getCustomerInvoices']);
