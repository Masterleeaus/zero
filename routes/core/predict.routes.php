<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('app.dashboard_throttle', '120,1')])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('predict')->as('predict.')->group(static function () {
            // Active predictions list
            Route::get('/', [\App\Http\Controllers\Predict\TitanPredictController::class, 'index'])
                ->name('index');

            // Asset failure prediction
            Route::post('asset/{asset_id}', [\App\Http\Controllers\Predict\TitanPredictController::class, 'asset'])
                ->name('asset');

            // SLA risk prediction
            Route::post('agreement/{agreement_id}', [\App\Http\Controllers\Predict\TitanPredictController::class, 'agreement'])
                ->name('agreement');

            // Capacity gap prediction
            Route::post('capacity', [\App\Http\Controllers\Predict\TitanPredictController::class, 'capacity'])
                ->name('capacity');

            // Dismiss a prediction
            Route::post('{prediction_id}/dismiss', [\App\Http\Controllers\Predict\TitanPredictController::class, 'dismiss'])
                ->name('dismiss');

            // Record outcome feedback
            Route::post('{prediction_id}/feedback', [\App\Http\Controllers\Predict\TitanPredictController::class, 'feedback'])
                ->name('feedback');
        });
    });
