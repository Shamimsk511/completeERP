<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ColorentManagementController;

Route::prefix('colorents')->name('colorents.')->group(function () {
    Route::get('/management', [ColorentManagementController::class, 'index'])->name('management');
    Route::get('/management/export', [ColorentManagementController::class, 'exportMovements'])->name('management.export');
    Route::get('/create', [ColorentManagementController::class, 'create'])->name('create');
    Route::post('/', [ColorentManagementController::class, 'store'])->name('store');
    Route::get('/{colorent}/edit', [ColorentManagementController::class, 'edit'])->name('edit');
    Route::put('/{colorent}', [ColorentManagementController::class, 'update'])->name('update');
    Route::post('/{id}/update-stock', [ColorentManagementController::class, 'updateStock'])->name('updateStock');
    Route::post('/{id}/update-price', [ColorentManagementController::class, 'updatePrice'])->name('updatePrice');
    Route::post('/purchases', [ColorentManagementController::class, 'storePurchase'])->name('purchases.store');
    Route::post('/usage', [ColorentManagementController::class, 'storeUsage'])->name('usage.store');
});
