<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RemainingProductsController;

Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/remaining-products', [RemainingProductsController::class, 'index'])->name('remaining_products');
    Route::get('/remaining-products/data', [RemainingProductsController::class, 'getUndeliveredItemsData'])->name('undelivered_items.data');
});
