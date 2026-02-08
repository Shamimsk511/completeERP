<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReferrerController;

Route::patch('referrers/{referrer}/invoices/{invoice}/compensation', [ReferrerController::class, 'updateInvoiceCompensation'])
    ->name('referrers.invoices.compensation');

Route::resource('referrers', ReferrerController::class);
