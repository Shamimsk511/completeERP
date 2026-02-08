<?php

use App\Http\Controllers\DatabaseUpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Database Update Routes
|--------------------------------------------------------------------------
|
| These routes handle the web-based database update functionality
| for shared hosting environments without terminal access.
|
*/

Route::middleware(['auth', 'role:Super Admin'])->prefix('system')->group(function () {
    Route::get('/adminlte-config', [App\Http\Controllers\SystemManagementController::class, 'adminlteConfig'])
        ->middleware('role:Super Admin')
        ->name('system.adminlte-config');
    // Database update page
    Route::get('/update', [DatabaseUpdateController::class, 'index'])->name('system.update');

    // Run all migrations
    Route::post('/update/migrate', [DatabaseUpdateController::class, 'runMigrations'])->name('system.update.migrate');

    // Run single migration
    Route::post('/update/migrate-single', [DatabaseUpdateController::class, 'runSingleMigration'])->name('system.update.migrate-single');

    // Check database connection and tables
    Route::get('/update/check-database', [DatabaseUpdateController::class, 'checkDatabase'])->name('system.update.check-database');

    // Clear caches
    Route::post('/update/clear-cache', [DatabaseUpdateController::class, 'clearCaches'])->name('system.update.clear-cache');

    // Optimize application
    Route::post('/update/optimize', [DatabaseUpdateController::class, 'optimize'])->name('system.update.optimize');

    // Toggle debug mode
    Route::post('/update/toggle-debug', [DatabaseUpdateController::class, 'toggleDebug'])->name('system.update.toggle-debug');

    // Create storage link
    Route::post('/update/storage-link', [DatabaseUpdateController::class, 'createStorageLink'])->name('system.update.storage-link');

    // Run seeders
    Route::post('/update/seed', [DatabaseUpdateController::class, 'runSeeder'])->name('system.update.seed');
    Route::get('/update/seeders', [DatabaseUpdateController::class, 'getSeeders'])->name('system.update.seeders');

    // Seed chart of accounts (for new expense accounts)
    Route::post('/update/seed-accounts', [DatabaseUpdateController::class, 'seedAccounts'])->name('system.update.seed-accounts');

    // Sync customers and companies to ledger accounts
    Route::post('/update/sync-customers-ledger', [DatabaseUpdateController::class, 'syncCustomersToLedger'])->name('system.update.sync-customers-ledger');

    // Backfill opening balances
    Route::post('/update/backfill-opening-balances', [DatabaseUpdateController::class, 'backfillOpeningBalances'])->name('system.update.backfill-opening-balances');

    // Assign default godown to products
    Route::post('/update/assign-default-godown', [DatabaseUpdateController::class, 'assignDefaultGodownToProducts'])->name('system.update.assign-default-godown');

    // Fix common issues
    Route::post('/update/fix-issues', [DatabaseUpdateController::class, 'fixCommonIssues'])->name('system.update.fix-issues');
});
