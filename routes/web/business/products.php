<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Product data routes
Route::get('products/get-products', [ProductController::class, 'getProducts'])->name('products.getProducts');
Route::get('products/group-summary', [ProductController::class, 'getProductGroupSummary'])->name('products.group-summary');

// Product imports
Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
Route::get('products/template/download', [ProductController::class, 'downloadTemplate'])->name('products.template.download');

// Reports main page
Route::get('/products/reports', [ProductController::class, 'reportsIndex'])->name('products.reports.index');

// Stock Report routes
Route::get('/products/reports/stock-data', [ProductController::class, 'getStockReport'])->name('products.reports.stock');
Route::get('/products/reports/stock-export', [ProductController::class, 'exportStockReport'])->name('products.reports.stock.export');

// Stock Value Report routes
Route::get('/products/reports/value-data', [ProductController::class, 'getStockValueReport'])->name('products.reports.value');
Route::get('/products/reports/value-export', [ProductController::class, 'exportStockValueReport'])->name('products.reports.value.export');

// Stock Adjustment routes
Route::get('/products/stock-adjustment', [ProductController::class, 'stockAdjustment'])->name('products.stock-adjustment');
Route::get('/products/stock-adjustment/data', [ProductController::class, 'getStockAdjustmentData'])->name('products.stock-adjustment.data');
Route::post('/products/stock-adjustment/save', [ProductController::class, 'saveStockAdjustment'])->name('products.stock-adjustment.save');
Route::get('/products/stock-adjustment/print', [ProductController::class, 'printStockCount'])->name('products.stock-adjustment.print');

// Merge Duplicates routes
Route::get('/products/merge-duplicates/search', [ProductController::class, 'searchProductsForMerge'])->name('products.merge.search');
Route::get('/products/merge-duplicates/duplicates', [ProductController::class, 'getDuplicateProducts'])->name('products.merge.duplicates');
Route::post('/products/merge-duplicates', [ProductController::class, 'mergeDuplicates'])->name('products.merge');

// Check duplicate product name
Route::get('/products/check-duplicate', [ProductController::class, 'checkDuplicateName'])->name('products.check-duplicate');

Route::resource('products', ProductController::class);
