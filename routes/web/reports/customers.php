<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerInsightController;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/customers', [CustomerInsightController::class, 'index'])->name('customers.index');
    Route::get('/customers/data/summary', [CustomerInsightController::class, 'customerSummaryData'])->name('customers.data.summary');
    Route::get('/customers/data/category-customers', [CustomerInsightController::class, 'categoryCustomerTotalsData'])->name('customers.data.category-customers');
    Route::get('/customers/data/category-top', [CustomerInsightController::class, 'categoryTopCustomerData'])->name('customers.data.category-top');
    Route::get('/customers/data/company-top', [CustomerInsightController::class, 'companyTopCustomerData'])->name('customers.data.company-top');
    Route::get('/customers/export/summary', [CustomerInsightController::class, 'exportCustomerSummary'])->name('customers.export.summary');
    Route::get('/customers/export/category-customers', [CustomerInsightController::class, 'exportCategoryCustomerTotals'])->name('customers.export.category-customers');
    Route::get('/customers/export/category-top', [CustomerInsightController::class, 'exportCategoryTopCustomer'])->name('customers.export.category-top');
    Route::get('/customers/export/company-top', [CustomerInsightController::class, 'exportCompanyTopCustomer'])->name('customers.export.company-top');
});
