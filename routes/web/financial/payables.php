<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayeeController;
use App\Http\Controllers\PayableTransactionController;

Route::prefix('payables')->group(function () {
    // Payees management
    Route::resource('payees', PayeeController::class);
    Route::get('/payees/{payee}/ledger', [PayeeController::class, 'ledger'])->name('payees.ledger');
    Route::get('/payees/{payee}/print-ledger', [PayeeController::class, 'printLedger'])->name('payees.print-ledger');
    Route::post('/payees/{payee}/accrue-interest', [PayeeController::class, 'accrueInterest'])->name('payees.accrue-interest');
    Route::post('/payees/{payee}/pay-interest', [PayeeController::class, 'payInterest'])->name('payees.pay-interest');
    Route::post('/payees/{payee}/kisti-skip', [PayeeController::class, 'addKistiSkip'])->name('payees.kisti-skip');
    
    // Payable transactions
    Route::prefix('transactions')->name('payable-transactions.')->group(function () {
        Route::get('/', [PayableTransactionController::class, 'index'])->name('index');
        Route::get('/create', [PayableTransactionController::class, 'create'])->name('create');
        Route::post('/', [PayableTransactionController::class, 'store'])->name('store');
        Route::get('/{payableTransaction}/edit', [PayableTransactionController::class, 'edit'])->name('edit');
        Route::put('/{payableTransaction}', [PayableTransactionController::class, 'update'])->name('update');
        Route::delete('/{payableTransaction}', [PayableTransactionController::class, 'destroy'])->name('destroy');
    });
});
