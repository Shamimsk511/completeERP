<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreditCustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MobileCustomerController;
use App\Http\Controllers\Api\MobileDecorCalculatorController;
use App\Http\Controllers\Api\MobileInvoiceCreateController;
use App\Http\Controllers\Api\MobileNotificationController;
use App\Http\Controllers\Api\MobileOtherDeliveryController;
use App\Http\Controllers\Api\MobilePurchaseController;
use App\Http\Controllers\Api\MobileReferenceController;
use App\Http\Controllers\Api\MobileReportController;
use App\Http\Controllers\Api\MobileTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/companies', [AuthController::class, 'loginCompanies']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::get('/invoices/{invoice}/print-pdf', [InvoiceController::class, 'printPdf']);
    Route::get('/customers/credit', [CreditCustomerController::class, 'index']);
    Route::get('/mobile/suppliers', [MobileReferenceController::class, 'suppliers']);
    Route::get('/mobile/products', [MobileReferenceController::class, 'products']);
    Route::get('/mobile/decor-categories', [MobileReferenceController::class, 'decorCategories']);
    Route::get('/mobile/customers', [MobileCustomerController::class, 'index']);
    Route::get('/mobile/customers/{customer}/open-invoices', [MobileCustomerController::class, 'openInvoices']);
    Route::post('/mobile/invoices', [MobileInvoiceCreateController::class, 'store']);
    Route::post('/mobile/purchases', [MobilePurchaseController::class, 'store']);
    Route::post('/mobile/other-deliveries', [MobileOtherDeliveryController::class, 'store']);
    Route::get('/mobile/transactions', [MobileTransactionController::class, 'index']);
    Route::post('/mobile/transactions', [MobileTransactionController::class, 'store']);
    Route::get('/mobile/reports/summary', [MobileReportController::class, 'summary']);
    Route::get('/mobile/notifications', [MobileNotificationController::class, 'index']);
    Route::get('/mobile/notifications/unread-count', [MobileNotificationController::class, 'unreadCount']);
    Route::post('/mobile/notifications/mark-read', [MobileNotificationController::class, 'markRead']);
    Route::post('/mobile/notifications/mark-all-read', [MobileNotificationController::class, 'markAllRead']);
    Route::get('/mobile/notifications/settings', [MobileNotificationController::class, 'settings']);
    Route::post('/mobile/notifications/settings', [MobileNotificationController::class, 'updateSettings']);
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
