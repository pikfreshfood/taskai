<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\TaskAiPortalController as TaskAiPortalApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/taskai/download', [TaskAiPortalApiController::class, 'download'])->name('taskai.download');
Route::get('/taskai/download/{update}', [TaskAiPortalApiController::class, 'download'])->name('taskai.download.update');
Route::get('/taskai/payment/callback', [TaskAiPortalApiController::class, 'paymentCallback'])->name('taskai.payment.callback');

Route::get('/admin/login', function (Request $request) {
    $request->session()->put('url.intended', route('admin.dashboard'));

    return redirect()->route('login')->with('status', 'Sign in with an admin account.');
})->middleware('guest')->name('admin.login');

// Dashboard (redirect to admin)
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [DashboardController::class, 'users'])->name('users');
    Route::delete('/users/{user}', [DashboardController::class, 'deleteUser'])->name('users.delete');
    Route::patch('/users/{user}/upgrade', [DashboardController::class, 'upgradeUser'])->name('users.upgrade');
    Route::get('/updates', [DashboardController::class, 'updates'])->name('updates');
    Route::get('/payments', [DashboardController::class, 'payments'])->name('payments');
    Route::get('/authorization', [DashboardController::class, 'authorization'])->name('authorization');
    Route::post('/authorization', [DashboardController::class, 'updateAuthorization'])->name('authorization.update');
    Route::post('/taskai-update', [DashboardController::class, 'publishAppUpdate'])->name('taskai-update.publish');
    Route::patch('/taskai-update/{update}', [DashboardController::class, 'updateAppUpdate'])->name('taskai-update.update');
    Route::delete('/taskai-update/{update}', [DashboardController::class, 'deleteAppUpdate'])->name('taskai-update.delete');
    Route::patch('/taskai-payments/{payment}/approve', [DashboardController::class, 'approvePayment'])->name('taskai-payments.approve');
    Route::get('/plans', [DashboardController::class, 'plans'])->name('plans');
    Route::post('/plans', [DashboardController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [DashboardController::class, 'updatePlan'])->name('plans.update');
    Route::delete('/plans/{plan}', [DashboardController::class, 'destroyPlan'])->name('plans.destroy');
});

require __DIR__.'/auth.php';
