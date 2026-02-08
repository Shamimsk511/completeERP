<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashFlowReportController;

Route::prefix('reports/cash-flow')->name('cash-flow.')->group(function () {
    Route::get('/', [CashFlowReportController::class, 'index'])->name('index');
    Route::get('/sales', [CashFlowReportController::class, 'getSalesReport'])->name('sales');
    Route::get('/sales-insights', [CashFlowReportController::class, 'getSalesInsights'])->name('sales-insights');
    Route::get('/collections', [CashFlowReportController::class, 'getCollectionReport'])->name('collections');
    Route::get('/purchases', [CashFlowReportController::class, 'getPurchaseReport'])->name('purchases');
    Route::get('/summary', [CashFlowReportController::class, 'getCashFlowSummary'])->name('summary');
    
    // Export routes
    Route::get('/export-sales', [CashFlowReportController::class, 'exportSalesReport'])->name('export-sales');
    Route::get('/export-collections', [CashFlowReportController::class, 'exportCollectionReport'])->name('export-collections');
    Route::get('/export-purchases', [CashFlowReportController::class, 'exportPurchaseReport'])->name('export-purchases');
    Route::get('/export-cashflow', [CashFlowReportController::class, 'exportCashFlowReport'])->name('export-cashflow');
});
