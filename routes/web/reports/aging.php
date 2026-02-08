<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayeeController;

Route::prefix('aging')->name('aging.')->group(function () {
    Route::get('/', [PayeeController::class, 'agingReport'])->name('index');
    Route::get('/detailed', [PayeeController::class, 'detailedAgingReport'])->name('detailed');
});
