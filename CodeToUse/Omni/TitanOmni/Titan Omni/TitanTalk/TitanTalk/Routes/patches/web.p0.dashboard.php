<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Http\Controllers\DashboardController;
use Modules\TitanTalk\Http\Controllers\HealthController;
use Modules\TitanTalk\Http\Controllers\SettingsController;

Route::middleware(['web','auth'])
    ->prefix('aiconverse')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('titantalk.dashboard');
        Route::get('/health/ping', [HealthController::class, 'ping'])->name('titantalk.health.ping');

        Route::get('/settings', [SettingsController::class, 'index'])->name('titantalk.settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('titantalk.settings.update');
    });
