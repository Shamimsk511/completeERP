<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\AccountGroupController;
use App\Http\Controllers\Accounting\AccountController;
use App\Http\Controllers\Accounting\BankController;
use App\Http\Controllers\Accounting\BankTransactionController;
use App\Http\Controllers\Accounting\ReportController;

/*
|--------------------------------------------------------------------------
| Accounting Module Routes
|--------------------------------------------------------------------------
*/

Route::prefix('accounting')->name('accounting.')->group(function () {

    // Account Groups (Chart of Accounts structure)
    Route::get('account-groups/tree', [AccountGroupController::class, 'tree'])->name('account-groups.tree');
    Route::resource('account-groups', AccountGroupController::class);

    // Accounts (Ledgers)
    Route::get('accounts/data', [AccountController::class, 'data'])->name('accounts.data');
    Route::get('accounts/customer/{customerId}', [AccountController::class, 'getCustomerAccount'])->name('accounts.customer');
    Route::get('accounts/{account}/ledger', [AccountController::class, 'ledger'])->name('accounts.ledger');
    Route::get('accounts/{account}/ledger/print', [AccountController::class, 'printLedger'])->name('accounts.ledger.print');
    Route::post('accounts/sync-customers-companies', [AccountController::class, 'syncCustomersAndCompanies'])->name('accounts.sync');
    Route::post('accounts/clear-cache', [AccountController::class, 'clearCache'])->name('accounts.clear-cache');
    Route::resource('accounts', AccountController::class);

    // Bank Management
    Route::resource('banks', BankController::class);
    Route::resource('bank-transactions', BankTransactionController::class)->except(['show']);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('profit-loss', [ReportController::class, 'profitAndLoss'])->name('profit-loss');
        Route::get('day-book', [ReportController::class, 'dayBook'])->name('day-book');
        Route::get('cash-book', [ReportController::class, 'cashBook'])->name('cash-book');
        Route::get('bank-book', [ReportController::class, 'bankBook'])->name('bank-book');
    });
});
