<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GodownController;

Route::resource('godowns', GodownController::class);
