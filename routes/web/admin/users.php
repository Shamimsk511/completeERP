<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::resource('users', UserController::class);

Route::get('/settings/users', function() {
    return redirect()->route('users.index');
})->name('settings.users');
