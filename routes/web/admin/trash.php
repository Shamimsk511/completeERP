<?php

use App\Http\Controllers\Admin\TrashController;
use Illuminate\Support\Facades\Route;

Route::prefix('trash')
    ->name('trash.')
    ->middleware(['role:Admin|Super Admin'])
    ->group(function () {
        Route::get('/', [TrashController::class, 'index'])->name('index');
        Route::post('/{type}/{id}/restore', [TrashController::class, 'restore'])->name('restore');
        Route::delete('/{type}/{id}', [TrashController::class, 'forceDelete'])->name('force-delete');
    });

