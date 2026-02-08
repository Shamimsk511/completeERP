<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessSettingController;
use App\Http\Controllers\ErpFeatureSettingController;

Route::prefix('business-settings')->name('business-settings.')->group(function () {
    Route::get('/', [BusinessSettingController::class, 'index'])->name('index');
    Route::put('/', [BusinessSettingController::class, 'update'])->name('update');
});

// ERP Feature Settings
Route::prefix('erp-settings')->name('erp-settings.')->group(function () {
    Route::get('/features', [ErpFeatureSettingController::class, 'index'])->name('features');
    Route::put('/features', [ErpFeatureSettingController::class, 'update'])->name('features.update');
    Route::post('/features/toggle', [ErpFeatureSettingController::class, 'toggle'])->name('features.toggle');
    Route::post('/features/seed', [ErpFeatureSettingController::class, 'seedDefaults'])->name('features.seed');
});
