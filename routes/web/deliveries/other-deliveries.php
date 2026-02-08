<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtherDeliveryController;

Route::prefix('other-deliveries')->name('other-deliveries.')->group(function () {
    // Main CRUD routes
    Route::get('/', [OtherDeliveryController::class, 'index'])->name('index');
    Route::get('/create', [OtherDeliveryController::class, 'create'])->name('create');
    Route::post('/', [OtherDeliveryController::class, 'store'])->name('store');
    
    // Stats and Export routes (before parameterized routes)
    Route::get('/stats', [OtherDeliveryController::class, 'stats'])->name('stats');
    Route::get('/export', [OtherDeliveryController::class, 'export'])->name('export');
    
    // Bulk operations (before parameterized routes)
    Route::post('/bulk-status-update', [OtherDeliveryController::class, 'bulkUpdateStatus'])->name('bulk-status-update');
    
    // Recipient functionality routes (before parameterized routes)
    Route::get('/recipient/{name}/history', [OtherDeliveryController::class, 'recipientHistory'])->name('recipient-history');
    Route::get('/recipient/{name}/history-ajax', [OtherDeliveryController::class, 'getRecipientHistory'])->name('recipient-history-ajax');
    
    // Individual delivery routes (parameterized routes should come last)
    Route::get('/{otherDelivery}', [OtherDeliveryController::class, 'show'])->name('show');
    Route::get('/{otherDelivery}/edit', [OtherDeliveryController::class, 'edit'])->name('edit');
    Route::put('/{otherDelivery}', [OtherDeliveryController::class, 'update'])->name('update');
    Route::delete('/{otherDelivery}', [OtherDeliveryController::class, 'destroy'])->name('destroy');
    Route::get('/{otherDelivery}/print', [OtherDeliveryController::class, 'print'])->name('print');
    
    // Status update routes - Using PUT method for consistency
    Route::put('/{otherDelivery}/update-status', [OtherDeliveryController::class, 'updateStatus'])->name('update-status');
});
