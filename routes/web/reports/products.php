<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductReportController;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/products', [ProductReportController::class, 'index'])->name('products.index');
    Route::get('/products/data/movement-products', [ProductReportController::class, 'movementProductsData'])->name('products.data.movement-products');
    Route::get('/products/data/non-moving-products', [ProductReportController::class, 'nonMovingProductsData'])->name('products.data.non-moving-products');
    Route::get('/products/data/company-summary', [ProductReportController::class, 'companySummaryData'])->name('products.data.company-summary');
    Route::get('/products/data/category-summary', [ProductReportController::class, 'categorySummaryData'])->name('products.data.category-summary');
    Route::get('/products/data/category-company-summary', [ProductReportController::class, 'categoryCompanySummaryData'])->name('products.data.category-company-summary');
    Route::get('/products/export/movement-products', [ProductReportController::class, 'exportMovementProducts'])->name('products.export.movement-products');
    Route::get('/products/export/non-moving-products', [ProductReportController::class, 'exportNonMovingProducts'])->name('products.export.non-moving-products');
    Route::get('/products/export/company-summary', [ProductReportController::class, 'exportCompanySummary'])->name('products.export.company-summary');
    Route::get('/products/export/category-summary', [ProductReportController::class, 'exportCategorySummary'])->name('products.export.category-summary');
    Route::get('/products/export/category-company-summary', [ProductReportController::class, 'exportCategoryCompanySummary'])->name('products.export.category-company-summary');
    Route::get('/products/sales', [ProductReportController::class, 'salesReport'])->name('products.sales');
    Route::get('/products/returns', [ProductReportController::class, 'returnsReport'])->name('products.returns');
    Route::get('/products/purchases', [ProductReportController::class, 'purchasesReport'])->name('products.purchases');
    Route::get('/products/other-deliveries', [ProductReportController::class, 'otherDeliveriesReport'])->name('products.other-deliveries');
    Route::get('/products/consolidated', [ProductReportController::class, 'consolidatedReport'])->name('products.consolidated');
    
    // Detail routes for AJAX requests
    Route::get('/products/sales/details', [ProductReportController::class, 'getProductSaleDetails'])->name('products.sales.details');
    Route::get('/products/returns/details', [ProductReportController::class, 'getProductReturnDetails'])->name('products.returns.details');
    Route::get('/products/purchases/details', [ProductReportController::class, 'getProductPurchaseDetails'])->name('products.purchases.details');
    Route::get('/products/other-deliveries/details', [ProductReportController::class, 'getProductDeliveryDetails'])->name('products.other-deliveries.details');
});
