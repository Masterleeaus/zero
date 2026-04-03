<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Http\Controllers\DashboardController;
use Modules\TitanTalk\Http\Controllers\AIConverseController;
use Modules\TitanTalk\Http\Controllers\SettingsController;

// Primary Titan Talk dashboard, used by sidebar/menu injections
Route::middleware(['web', 'auth'])
    ->prefix('account/titantalk')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->middleware(['titantalk.tenant'])
            ->name('titantalk.dashboard');
    
        Route::get('/settings', [SettingsController::class, 'index'])
            ->middleware(['titantalk.tenant', 'titantalk.perm:titantalk.settings.manage'])
            ->name('titantalk.settings');
        Route::post('/settings', [SettingsController::class, 'save'])
            ->middleware(['titantalk.tenant', 'titantalk.perm:titantalk.settings.manage'])
            ->name('titantalk.settings.save');
});

// Legacy AIConverse endpoint (kept for backward compatibility / pings)
Route::middleware(['web'])
    ->prefix('aiconverse')
    ->group(function () {
        Route::get('/', [AIConverseController::class, 'index'])
            ->name('titantalk.index');
        Route::get('/ping', fn () => 'TitanTalk OK')
            ->name('titantalk.ping');
    });
