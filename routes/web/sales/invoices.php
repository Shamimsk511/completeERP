<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;


Route::get('/product-details/{id}', [InvoiceController::class, 'getProductDetails']);
// Invoice data and summary routes
Route::get('invoices/data', [InvoiceController::class, 'dataTable'])->name('invoices.data');
Route::get('invoices/summary', [InvoiceController::class, 'getSummary'])->name('invoices.summary');
Route::get('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
Route::get('/invoices/statistics', [InvoiceController::class, 'getStatistics'])->name('invoices.statistics');
Route::get('invoices/get-summary', [InvoiceController::class, 'getSummary'])->name('invoices.getSummary');
// Invoice creation routes
Route::get('/invoices/create-other', [InvoiceController::class, 'createOther'])->name('invoices.create-other');

// Invoice status management
Route::post('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');
Route::post('invoices/update-status', [InvoiceController::class, 'updateStatus'])->name('invoices.update.status');
Route::post('/invoices/update-delivery-status', [InvoiceController::class, 'updateDeliveryStatus'])->name('invoices.update-delivery-status');
Route::patch('/invoices/{invoice}/referrer-compensation', [InvoiceController::class, 'updateReferrerCompensation'])->name('invoices.referrer-compensation');

// Invoice printing and details
Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
Route::get('product-details/{id}', [InvoiceController::class, 'getProductDetails'])->name('product.details');
Route::get('customer-details/{id}', [InvoiceController::class, 'getCustomerDetails'])->name('customer.details');
Route::get('/invoices/{invoice}/modal-details', [InvoiceController::class, 'getModalDetails'])
    ->name('invoices.modal-details');
    
// Customer purchase history
Route::get('/customer-purchase-history/{customer}', [InvoiceController::class, 'getCustomerPurchaseHistory'])->name('customer.purchase-history');

Route::resource('invoices', InvoiceController::class);
