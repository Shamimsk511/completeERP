<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseController;

Route::resource('purchases', PurchaseController::class);

// Purchase-specific functionality
Route::post('purchases/create-product', [PurchaseController::class, 'createProduct'])->name('purchases.createProduct');
Route::get('purchases/search-products', [PurchaseController::class, 'searchProducts'])->name('purchases.searchProducts');
