<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflow\Http\Controllers\Api\HealthController;
use Modules\Workflow\Http\Controllers\SettingsController;

/**
 * Workflow module API routes
 * Note: When imported into RestAPI Add-ons Bridge, these will be nested under /api/addons/workflow/...
 */

// Health
Route::prefix('workflow')
    ->middleware(['api'])
    ->group(function () {
        Route::get('/ping', [HealthController::class, 'ping']);
    });

// Settings (protected)
Route::middleware(['api','auth:api','permission:manage_workflow'])
    ->prefix('workflow')
    ->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('workflow.settings.api');
        Route::post('/settings', [SettingsController::class, 'update'])->name('workflow.settings.update.api');
    });
