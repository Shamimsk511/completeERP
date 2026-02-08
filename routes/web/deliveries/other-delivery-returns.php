<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtherDeliveryReturnController;

Route::resource('other-delivery-returns', OtherDeliveryReturnController::class);
Route::get('other-delivery-returns/{otherDeliveryReturn}/print', [OtherDeliveryReturnController::class, 'print'])
    ->name('other-delivery-returns.print');
Route::patch('other-delivery-returns/{otherDeliveryReturn}/update-status', [OtherDeliveryReturnController::class, 'updateStatus'])
    ->name('other-delivery-returns.update-status');
