<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreditCustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MobileDecorCalculatorController;
use App\Http\Controllers\Api\MobileOtherDeliveryController;
use App\Http\Controllers\Api\MobilePurchaseController;
use App\Http\Controllers\Api\MobileReferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::get('/customers/credit', [CreditCustomerController::class, 'index']);
    Route::get('/mobile/suppliers', [MobileReferenceController::class, 'suppliers']);
    Route::get('/mobile/products', [MobileReferenceController::class, 'products']);
    Route::get('/mobile/decor-categories', [MobileReferenceController::class, 'decorCategories']);
    Route::post('/mobile/purchases', [MobilePurchaseController::class, 'store']);
    Route::post('/mobile/other-deliveries', [MobileOtherDeliveryController::class, 'store']);
    Route::post('/mobile/decor-calculator/calculate', [MobileDecorCalculatorController::class, 'calculate']);
    
    // Example protected route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Test route to check if API is working
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});
