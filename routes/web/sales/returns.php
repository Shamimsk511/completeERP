<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductReturnController;

// Data endpoint for server-side DataTables (must be before resource route)
Route::get('returns/data', [ProductReturnController::class, 'data'])->name('returns.data');

Route::resource('returns', ProductReturnController::class);

// Return printing
Route::get('returns/{return}/print', [ProductReturnController::class, 'print'])->name('returns.print');

// Customer and invoice data for returns
Route::get('customer-invoices/{customerId}', [ProductReturnController::class, 'getInvoicesByCustomer']);
Route::get('invoice-items/{invoiceId}', [ProductReturnController::class, 'getInvoiceItems']);

// FIXED: Consistent route patterns for returns
Route::get('returns/customer-purchase-history/{customerId}', [ProductReturnController::class, 'getCustomerPurchaseHistory'])
    ->name('returns.customer-purchase-history');

Route::get('returns/customer-details/{customerId}', [ProductReturnController::class, 'getCustomerDetails'])
    ->name('returns.customer-details');

Route::get('returns/product-details/{productId}', [ProductReturnController::class, 'getProductDetails'])
    ->name('returns.product-details');

// Return validation route
Route::post('returns/validate', [ProductReturnController::class, 'validateReturn'])
    ->name('returns.validate');

// Approve return route
Route::post('returns/{return}/approve', [ProductReturnController::class, 'approve'])
    ->name('returns.approve');