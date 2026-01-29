<?php

use Illuminate\Support\Facades\Route;
use AdaReach\Sms\Http\Controllers\DashboardController;

// Login route (no auth required)
Route::prefix(config('adarearch.dashboard.path', 'sms-dashboard'))
    ->middleware(['web'])
    ->group(function () {
        Route::get('/login', [DashboardController::class, 'loginForm'])->name('adarearch.login.form');
        Route::post('/login', [DashboardController::class, 'login'])->name('adarearch.login');
        Route::post('/logout', [DashboardController::class, 'logout'])->name('adarearch.logout');
    });

// Protected dashboard routes
Route::prefix(config('adarearch.dashboard.path', 'sms-dashboard'))
    ->middleware(['web', 'adarearch.auth'])
    ->group(function () {
        // API endpoints
        Route::prefix('api')->group(function () {
            Route::get('/sms', [DashboardController::class, 'list'])->name('adarearch.api.list');
            Route::get('/sms/{id}', [DashboardController::class, 'show'])->name('adarearch.api.show');
            Route::post('/sms/{id}/retry', [DashboardController::class, 'retry'])->name('adarearch.api.retry');
            Route::post('/send-sms', [DashboardController::class, 'sendSms'])->name('adarearch.api.send');
            Route::get('/stats', [DashboardController::class, 'stats'])->name('adarearch.api.stats');
            Route::get('/credits', [DashboardController::class, 'credits'])->name('adarearch.api.credits');
            Route::get('/overview', [DashboardController::class, 'overview'])->name('adarearch.api.overview');
            Route::get('/export', [DashboardController::class, 'export'])->name('adarearch.api.export');
            
            // Settings endpoints
            Route::get('/settings', [DashboardController::class, 'getSettings'])->name('adarearch.api.settings.get');
            Route::post('/settings', [DashboardController::class, 'updateSettings'])->name('adarearch.api.settings.update');
            Route::post('/settings/test-connection', [DashboardController::class, 'testConnection'])->name('adarearch.api.settings.test');
        });
        
        // Dashboard view - catch all routes for SPA (must be last)
        Route::get('/{any?}', [DashboardController::class, 'index'])
            ->where('any', '.*')
            ->name('adarearch.dashboard');
    });
