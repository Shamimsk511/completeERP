<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerSearchController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerPasswordController;

Route::prefix('customer')->name('customer.')->group(function () {
    
    // Root customer route - redirect based on auth status
    Route::get('/', function () {
        if (auth('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }
        return redirect()->route('customer.login');
    })->name('index');

    // Magic login via signed URL (QR)
    Route::get('magic-login/{customer}/{invoice}', [CustomerLoginController::class, 'magicLogin'])
        ->name('magic-login')
        ->middleware('signed');
    
    // Guest routes (customer not logged in)
    Route::middleware('guest:customer')->group(function () {
        Route::get('login', [CustomerLoginController::class, 'showLoginForm'])
            ->name('login');
        Route::post('login', [CustomerLoginController::class, 'login'])
            ->middleware('throttle:10,1');
    });

    // Authenticated customer routes (customer logged in)
    Route::middleware('auth:customer')->group(function () {
        Route::get('dashboard', [CustomerDashboardController::class, 'index'])
            ->name('dashboard');
            
        Route::get('invoices', [CustomerDashboardController::class, 'invoices'])
            ->name('invoices');
            
        Route::get('invoices/{invoice}', [CustomerDashboardController::class, 'invoiceDetails'])
            ->name('invoices.show');

        Route::get('ledger', [CustomerDashboardController::class, 'ledger'])
            ->name('ledger');
        
        Route::get('{customer}/transactions', [CustomerDashboardController::class, 'transactions'])
            ->name('transactions');
            
        Route::get('profile', [CustomerDashboardController::class, 'profile'])
            ->name('profile');

        Route::get('password', [CustomerPasswordController::class, 'show'])
            ->name('password.show');
        Route::post('password', [CustomerPasswordController::class, 'update'])
            ->name('password.update');
        Route::post('password/skip', [CustomerPasswordController::class, 'skip'])
            ->name('password.skip');
            
        Route::post('logout', [CustomerLoginController::class, 'logout'])
            ->name('logout');
    });
});

// Customer search route (outside auth)
