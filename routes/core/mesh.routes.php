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

// ── TitanMesh: Inbound API (peer-to-peer mesh protocol) ──────────────────────
Route::prefix('api/mesh')
    ->as('api.mesh.')
    ->middleware(['throttle:60,1'])
    ->group(static function () {
        Route::post('handshake',          [\App\Http\Controllers\Api\Mesh\MeshNodeController::class, 'handshake'])
            ->name('handshake');
        Route::post('capabilities',       [\App\Http\Controllers\Api\Mesh\MeshNodeController::class, 'capabilities'])
            ->name('capabilities');
        Route::post('dispatch/offer',     [\App\Http\Controllers\Api\Mesh\MeshNodeController::class, 'offer'])
            ->name('dispatch.offer');
        Route::post('dispatch/accept',    [\App\Http\Controllers\Api\Mesh\MeshNodeController::class, 'accept'])
            ->name('dispatch.accept');
        Route::post('dispatch/complete',  [\App\Http\Controllers\Api\Mesh\MeshNodeController::class, 'complete'])
            ->name('dispatch.complete');
    });
