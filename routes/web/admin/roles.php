<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

Route::resource('roles', RoleController::class);

Route::get('/settings/roles', function() {
    return redirect()->route('roles.index');
})->name('settings.roles');
