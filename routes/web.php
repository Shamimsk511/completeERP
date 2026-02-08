<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\CustomerSearchController;
use App\Http\Controllers\EmployeeController;
Route::get('/employee/{id}', [EmployeeController::class, 'show'])
    ->middleware('auth:web')
    ->name('employee.show');
Route::get('/', [HomepageController::class, 'index'])->name('homepage');
Route::get('/sitemap.xml', [HomepageController::class, 'sitemap']);
Route::get('/robots.txt', [HomepageController::class, 'robots']);
Route::post('/switch-language', [HomepageController::class, 'switchLanguage']);

// Alternative homepage route
Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage.alt');

// CUSTOMER ROUTES - MUST BE OUTSIDE ADMIN AUTH
require __DIR__.'/customer.php';

// ADMIN ROUTES - Protected by admin auth
Route::middleware(['auth:web', 'verified', 'feature', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:Super Admin')->group(function () {
        Route::resource('tenants', App\Http\Controllers\TenantController::class)
            ->except(['show'])
            ->middleware('role:Super Admin');

        Route::post('/tenants/{tenant}/backups', [App\Http\Controllers\TenantBackupController::class, 'create'])
            ->name('tenants.backups.create');
        Route::post('/tenants/{tenant}/backups/restore', [App\Http\Controllers\TenantBackupController::class, 'restore'])
            ->name('tenants.backups.restore');
        Route::get('/tenants/{tenant}/backups/{filename}', [App\Http\Controllers\TenantBackupController::class, 'download'])
            ->name('tenants.backups.download');
        Route::delete('/tenants/{tenant}/backups/{filename}', [App\Http\Controllers\TenantBackupController::class, 'delete'])
            ->name('tenants.backups.delete');
    });

    Route::get('/tenants/select', [App\Http\Controllers\TenantController::class, 'select'])->name('tenants.select');
    Route::post('/tenants/switch', [App\Http\Controllers\TenantController::class, 'switch'])->name('tenants.switch');
    Route::post('/tenants/assign-existing', [App\Http\Controllers\TenantController::class, 'assignExisting'])
        ->middleware('role:Super Admin')
        ->name('tenants.assign-existing');
    

    Route::middleware(['auth', 'verified'])->group(function () {
    
    // SMS Dashboard
    Route::get('/sms/dashboard', [App\Http\Controllers\SmsController::class, 'dashboard'])
         ->name('sms.dashboard');

    // SMS Settings Management
    Route::prefix('sms/settings')->name('sms.settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SmsController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\SmsController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\SmsController::class, 'store'])->name('store');
        Route::get('/{smsSettings}/edit', [App\Http\Controllers\SmsController::class, 'edit'])->name('edit');
        Route::put('/{smsSettings}', [App\Http\Controllers\SmsController::class, 'update'])->name('update');
        Route::delete('/{smsSettings}', [App\Http\Controllers\SmsController::class, 'destroy'])->name('destroy');
    });

    // SMS Actions
    Route::prefix('sms/actions')->name('sms.')->group(function () {
        Route::post('/toggle', [App\Http\Controllers\SmsController::class, 'toggleSms'])->name('toggle');
        Route::post('/set-active', [App\Http\Controllers\SmsController::class, 'setActive'])->name('set-active');
        Route::post('/check-balance', [App\Http\Controllers\SmsController::class, 'checkBalance'])->name('check-balance');
        Route::get('/statistics', [App\Http\Controllers\SmsController::class, 'getStatistics'])->name('statistics');
        Route::post('/test', [App\Http\Controllers\SmsController::class, 'testSms'])->name('test');
        Route::post('/bulk', [App\Http\Controllers\SmsController::class, 'bulkSms'])->name('bulk');
    });

    // SMS Logs
    Route::prefix('sms/logs')->name('sms.logs.')->group(function () {
        Route::get('/', [App\Http\Controllers\SmsController::class, 'logs'])->name('index');
        Route::get('/{smsLog}/details', [App\Http\Controllers\SmsController::class, 'getLogDetails'])->name('details');
    });
});


    // Your existing admin routes here...
    require __DIR__.'/web/admin/system.php';
    require __DIR__.'/web/admin/users.php';
    require __DIR__.'/web/admin/roles.php';
    require __DIR__.'/web/admin/settings.php';
    require __DIR__.'/web/business/customers.php';
    require __DIR__.'/web/business/referrers.php';
    require __DIR__.'/web/business/products.php';
    require __DIR__.'/web/business/godowns.php';
    require __DIR__.'/web/business/categories.php';
    require __DIR__.'/web/business/companies.php';
    require __DIR__.'/web/business/transactions.php';
    require __DIR__.'/web/sales/invoices.php';
    require __DIR__.'/web/sales/challans.php';
    require __DIR__.'/web/sales/returns.php';
    require __DIR__.'/web/sales/remaining-products.php';
    require __DIR__.'/web/purchases/purchases.php';
    require __DIR__.'/web/deliveries/other-deliveries.php';
    require __DIR__.'/web/deliveries/other-delivery-returns.php';
    require __DIR__.'/web/hr/payroll.php';
    require __DIR__.'/web/reports/products.php';
    require __DIR__.'/web/reports/customers.php';
    require __DIR__.'/web/reports/cash-flow.php';
    require __DIR__.'/web/reports/aging.php';
    require __DIR__.'/web/financial/debt-collection.php';
    require __DIR__.'/web/financial/payables.php';
    require __DIR__.'/web/financial/expenses.php';
    if (config('features.cash_register')) {
        require __DIR__.'/web/financial/cash-registers.php';
    }
    require __DIR__.'/web/tools/decor-calculator.php';
    require __DIR__.'/web/tools/colorents.php';
    require __DIR__.'/web/communications/chat.php';
    require __DIR__.'/web/profile.php';
    require __DIR__.'/web/accounting/accounting.php';
    require __DIR__.'/web/system/update.php';
});

require __DIR__.'/auth.php';
