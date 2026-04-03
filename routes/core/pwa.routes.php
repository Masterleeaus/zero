<?php

use App\Http\Controllers\TitanPwa\PwaDiagnosticsController;
use App\Http\Controllers\TitanPwa\TitanPwaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Titan Zero PWA Routes
|--------------------------------------------------------------------------
|
| Public endpoints: manifest, bootstrap
| Auth-guarded: handshake, signals/ingest, sync/status, diagnostics
|
*/

Route::prefix('pwa')
    ->as('pwa.')
    ->group(static function () {

        // Public – served without auth (manifest + bootstrap config)
        Route::get('/manifest', [TitanPwaController::class, 'manifest'])->name('manifest');
        Route::get('/bootstrap', [TitanPwaController::class, 'bootstrap'])->name('bootstrap');

        // Auth-guarded PWA API endpoints
        Route::middleware(['auth', 'throttle:60,1'])
            ->group(static function () {
                Route::post('/handshake', [TitanPwaController::class, 'handshake'])->name('handshake');
                Route::post('/signals/ingest', [TitanPwaController::class, 'ingest'])->name('signals.ingest');
                Route::get('/sync/status', [TitanPwaController::class, 'syncStatus'])->name('sync.status');
            });

        // Operator diagnostics (auth-only, no throttle relaxation)
        Route::middleware(['auth'])
            ->prefix('diagnostics')
            ->as('diagnostics.')
            ->group(static function () {
                Route::get('/', [PwaDiagnosticsController::class, 'index'])->name('index');
                Route::get('/stats', [PwaDiagnosticsController::class, 'stats'])->name('stats');
                Route::post('/nodes/promote', [PwaDiagnosticsController::class, 'promoteNode'])->name('nodes.promote');
                Route::post('/nodes/clear-rate-limit', [PwaDiagnosticsController::class, 'clearRateLimit'])->name('nodes.clear-rate-limit');
            });
    });
