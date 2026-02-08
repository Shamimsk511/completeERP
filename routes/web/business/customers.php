<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;

// Customer import/export routes
Route::get('customers/import', [CustomerController::class, 'importForm'])->name('customers.import.form');
Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
Route::get('customers/export/template', [CustomerController::class, 'exportTemplate'])->name('customers.export.template');
Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');

// Customer data routes
Route::get('customers/get-customers', [CustomerController::class, 'getCustomers'])->name('customers.getCustomers');
Route::get('customers/search', [CustomerController::class, 'searchCustomers'])->name('customers.search');
Route::get('customers/check-duplicate', [CustomerController::class, 'checkDuplicate'])->name('customers.check-duplicate');
Route::get('customers/{customer}/purchase-summary-data', [CustomerController::class, 'purchaseSummaryData'])
    ->name('customers.purchase-summary.data');
Route::get('customers/{customer}/return-items-data', [CustomerController::class, 'returnItemsData'])
    ->name('customers.return-items.data');

// Customer ledger routes
Route::get('customers/{customer}/ledger/print', [CustomerController::class, 'printLedger'])->name('customers.ledger.print');

Route::resource('customers', CustomerController::class);
Route::get('customers/{customer}/login-info', [CustomerController::class, 'showLoginInfo'])
    ->name('customers.login-info');
Route::post('/customers/{customer}/send-sms', [CustomerController::class, 'sendSms'])->name('customers.send-sms');
