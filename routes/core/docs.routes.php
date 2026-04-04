<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('app.dashboard_throttle', '120,1')])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('docs')->as('docs.')->group(static function () {

            // Injected documents for a service job
            Route::get('jobs/{job}', [\App\Http\Controllers\Docs\DocsExecutionBridgeController::class, 'forJob'])
                ->name('jobs.documents');

            // Injected documents for an inspection
            Route::get('inspections/{inspection}', [\App\Http\Controllers\Docs\DocsExecutionBridgeController::class, 'forInspection'])
                ->name('inspections.documents');

            // Acknowledge a document within a job or inspection
            Route::post('acknowledge', [\App\Http\Controllers\Docs\DocsExecutionBridgeController::class, 'acknowledge'])
                ->name('acknowledge');

            // Semantic document search
            Route::get('search', [\App\Http\Controllers\Docs\DocsExecutionBridgeController::class, 'search'])
                ->name('search');

            // List injection rules for the authenticated company
            Route::get('rules', [\App\Http\Controllers\Docs\DocsExecutionBridgeController::class, 'rules'])
                ->name('rules.index');
        });
    });
