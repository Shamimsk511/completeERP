<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;

Route::prefix('expenses')->name('expenses.')->group(function () {
    Route::get('/', [ExpenseController::class, 'index'])->name('index');
    Route::get('/create', [ExpenseController::class, 'create'])->name('create');
    Route::post('/', [ExpenseController::class, 'store'])->name('store');
    Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
    Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
    Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');

    Route::get('/categories', [ExpenseCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [ExpenseCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [ExpenseCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [ExpenseCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [ExpenseCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [ExpenseCategoryController::class, 'destroy'])->name('categories.destroy');
});
