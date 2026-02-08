<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChallanController;

Route::resource('challans', ChallanController::class);

// Challan printing
Route::get('challan-print/{challan}', [ChallanController::class, 'print'])->name('challans.print');

// Quick challan creation from invoice delivery modal
Route::post('challans/quick-store', [ChallanController::class, 'quickStore'])->name('challans.quick-store');

// Invoice items for challans
Route::get('get-invoice-items/{invoice}', [ChallanController::class, 'getInvoiceItems'])->name('get-invoice-items');
