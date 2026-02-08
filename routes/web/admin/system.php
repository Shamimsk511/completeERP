<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemManagementController;

Route::prefix('system')->name('system.')->middleware('can:update,App\Models\User')->group(function () {
    Route::get('/', [SystemManagementController::class, 'index'])->name('index');
    
    // Cache Management
    Route::post('/cache/clear', [SystemManagementController::class, 'clearCache'])->name('cache.clear');
    Route::post('/cache/optimize', [SystemManagementController::class, 'optimizeCache'])->name('cache.optimize');
    
    // Database Backup & Restore
    Route::get('/backups', [SystemManagementController::class, 'backups'])->name('backups');
    Route::post('/create-backup', [SystemManagementController::class, 'createBackup'])->name('create-backup');
    Route::post('/create-full-backup', [SystemManagementController::class, 'createFullBackup'])->name('create-full-backup');
    Route::get('/download-backup/{filename}', [SystemManagementController::class, 'downloadBackup'])->name('download-backup');
    Route::delete('/delete-backup/{filename}', [SystemManagementController::class, 'deleteBackup'])->name('delete-backup');
    Route::post('/restore-backup', [SystemManagementController::class, 'restoreBackup'])->name('restore-backup');
    Route::post('/restore-upload', [SystemManagementController::class, 'restoreUpload'])->name('restore-upload');
    Route::post('/cleanup-backups', [SystemManagementController::class, 'cleanupBackups'])->name('cleanup-backups');
});
