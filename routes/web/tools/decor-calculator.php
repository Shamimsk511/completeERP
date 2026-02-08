<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DecorCalculatorController;
use App\Http\Controllers\Admin\TilesCategoryController;
use App\Http\Controllers\Admin\TilesSettingController;

Route::prefix('admin')->name('admin.')->group(function () {
    // Decor Calculator Routes
    Route::prefix('decor-calculator')->name('decor-calculator.')->group(function () {
        Route::get('/', [DecorCalculatorController::class, 'index'])->name('index');
        Route::get('/categories', [DecorCalculatorController::class, 'getCategories'])->name('categories');
        Route::get('/settings/{categoryId}', [DecorCalculatorController::class, 'getSettings'])->name('settings.show');
        Route::post('/calculate', [DecorCalculatorController::class, 'calculate'])->name('calculate');
        Route::post('/settings', [DecorCalculatorController::class, 'saveSettings'])->name('settings.save');
        Route::get('/component', [DecorCalculatorController::class, 'getComponent'])->name('component');
    });
    
    // Tiles Categories Management
    Route::resource('tiles-categories', TilesCategoryController::class);
    Route::post('tiles-categories/{tilesCategory}/settings', [TilesCategoryController::class, 'saveSettings'])
        ->name('tiles-categories.settings');
    
    // Tiles Settings Management
    Route::resource('tiles-settings', TilesSettingController::class);
});
