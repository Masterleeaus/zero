<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:120,1'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('insights')->as('insights.')->group(static function () {
            Route::get('overview', [\App\Http\Controllers\Core\Insights\InsightsController::class, 'overview'])
                ->name('overview');
            Route::get('reports', [\App\Http\Controllers\Core\Insights\InsightsController::class, 'reports'])
                ->name('reports');
        });
    });
