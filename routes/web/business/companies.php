<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;

Route::resource('companies', CompanyController::class);

Route::get('companies/{company}/data/recent-payments', [CompanyController::class, 'recentPaymentsData'])->name('companies.recent-payments');
Route::get('companies/{company}/data/top-products', [CompanyController::class, 'topProductsData'])->name('companies.top-products');
Route::get('companies/{company}/data/recent-purchases', [CompanyController::class, 'recentPurchasesData'])->name('companies.recent-purchases');
Route::get('companies/{company}/data/low-stock', [CompanyController::class, 'lowStockProductsData'])->name('companies.low-stock');
Route::get('companies/{company}/data/remaining-products', [CompanyController::class, 'remainingProductsData'])->name('companies.remaining-products');
