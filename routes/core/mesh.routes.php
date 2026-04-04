<?php

use Illuminate\Support\Facades\Route;

// ── TitanMesh: Internal Admin Dashboard ─────────────────────────────────────
Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('app.dashboard_throttle', '120,1')])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('mesh')->as('mesh.')->group(static function () {
            Route::get('nodes',       [\App\Http\Controllers\Mesh\MeshDashboardController::class, 'nodes'])
                ->name('nodes');
            Route::get('requests',    [\App\Http\Controllers\Mesh\MeshDashboardController::class, 'requests'])
                ->name('requests');
            Route::get('settlements', [\App\Http\Controllers\Mesh\MeshDashboardController::class, 'settlements'])
                ->name('settlements');
            Route::get('trust',       [\App\Http\Controllers\Mesh\MeshDashboardController::class, 'trust'])
                ->name('trust');
        });
    });
