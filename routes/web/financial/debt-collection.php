<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebtCollectionController;

Route::prefix('debt-collection')->name('debt-collection.')->group(function () {
    // Main dashboard
    Route::get('/', [DebtCollectionController::class, 'index'])->name('index');
    Route::get('/due-today', [DebtCollectionController::class, 'dueToday'])->name('due-today');
    Route::get('/due-this-week', [DebtCollectionController::class, 'dueThisWeek'])->name('due-this-week');
    
    // AJAX endpoints
    Route::get('/get-customers', [DebtCollectionController::class, 'getCustomersWithOutstanding'])->name('get-customers');
    Route::get('/get-customers-cards', [DebtCollectionController::class, 'getCustomersCards'])->name('get-customers-cards');
    Route::get('/stats', [DebtCollectionController::class, 'getStats'])->name('stats');
    
    // Call scheduling routes
    Route::get('/call-schedule', [DebtCollectionController::class, 'callSchedule'])->name('call-schedule');
    Route::get('/get-scheduled-calls', [DebtCollectionController::class, 'getScheduledCalls'])->name('get-scheduled-calls');
    Route::post('/schedule-call', [DebtCollectionController::class, 'scheduleCall'])->name('schedule-call');
    Route::put('/scheduled-calls/{scheduleId}', [DebtCollectionController::class, 'updateScheduledCall'])->name('update-scheduled-call');
    
    // Customer management
    Route::post('/customers/{customer}/call', [DebtCollectionController::class, 'logCall'])->name('log-call');
    Route::put('/customers/{customer}/tracking', [DebtCollectionController::class, 'updateTracking'])->name('update-tracking');
    Route::get('/customers/{customer}/call-history', [DebtCollectionController::class, 'callHistory'])->name('call-history');
    Route::get('/customers/{customer}/tracking/edit', [DebtCollectionController::class, 'editTracking'])->name('edit-tracking');
    Route::get('/customers/{customer}/call-history-data', [DebtCollectionController::class, 'getCallHistory'])->name('call-history-data');
    
    // Bulk actions
    Route::post('/bulk-action', [DebtCollectionController::class, 'bulkAction'])->name('bulk-action');
    
    // Reports
    Route::get('/analytics', [DebtCollectionController::class, 'analytics'])->name('analytics');
    Route::get('/reports/overdue', [DebtCollectionController::class, 'overdueAccounts'])->name('reports.overdue');
    Route::get('/reports/performance', [DebtCollectionController::class, 'performance'])->name('reports.performance');
    
    // Export
    Route::get('/export', [DebtCollectionController::class, 'export'])->name('export');
});
