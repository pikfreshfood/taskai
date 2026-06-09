<?php

use App\Http\Controllers\Api\TaskAiPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('taskai')->group(function () {
    Route::get('/app-update', [TaskAiPortalController::class, 'appUpdate']);
    Route::post('/register/request-otp', [TaskAiPortalController::class, 'requestRegistrationOtp']);
    Route::post('/register', [TaskAiPortalController::class, 'register']);
    Route::post('/login', [TaskAiPortalController::class, 'login']);
    Route::post('/forgot-password', [TaskAiPortalController::class, 'forgotPassword']);
    Route::post('/logout', [TaskAiPortalController::class, 'logout']);
    Route::get('/me', [TaskAiPortalController::class, 'me']);
    Route::post('/usage/sync', [TaskAiPortalController::class, 'syncUsage']);
    Route::post('/paystack/initialize', [TaskAiPortalController::class, 'initializePayment']);
    Route::get('/paystack/verify/{reference}', [TaskAiPortalController::class, 'verifyPayment']);
    Route::get('/plans', [TaskAiPortalController::class, 'plans']);
});
