<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashRegisterController;

// Cash Register Routes (Simplified)
Route::prefix('cash-registers')->name('cash-registers.')->group(function () {
    // List all registers
    Route::get('/', [CashRegisterController::class, 'index'])->name('index');

    // Open new register
    Route::get('/open', [CashRegisterController::class, 'open'])->name('open');
    Route::post('/', [CashRegisterController::class, 'store'])->name('store');

    // View single register
    Route::get('/{cashRegister}', [CashRegisterController::class, 'show'])->name('show');

    // Add transaction
    Route::post('/{cashRegister}/add-transaction', [CashRegisterController::class, 'addTransaction'])->name('add-transaction');

    // Close register
    Route::post('/{cashRegister}/close', [CashRegisterController::class, 'close'])->name('close');

    // Delete register
    Route::delete('/{cashRegister}', [CashRegisterController::class, 'destroy'])->name('destroy');

    // Reports
    Route::get('/reports/generate', [CashRegisterController::class, 'report'])->name('report');
});
