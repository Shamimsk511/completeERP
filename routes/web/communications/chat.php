<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/messages/{target}', [ChatController::class, 'messages'])
        ->where('target', 'all|[0-9]+')
        ->name('messages');
    Route::post('/messages', [ChatController::class, 'send'])->name('send');
    Route::get('/presence', [ChatController::class, 'presence'])->name('presence');
    Route::post('/ping', [ChatController::class, 'ping'])->name('ping');
    Route::get('/notifications', [ChatController::class, 'notifications'])->name('notifications');
    Route::get('/notifications/navbar', [ChatController::class, 'navbarNotifications'])->name('notifications.navbar');
    Route::post('/notifications/read', [ChatController::class, 'markNotificationsRead'])->name('notifications.read');
    Route::post('/notifications/{notification}/read', [ChatController::class, 'markNotificationRead'])
        ->name('notifications.read-one');
    Route::post('/clear', [ChatController::class, 'clear'])->name('clear');
});
