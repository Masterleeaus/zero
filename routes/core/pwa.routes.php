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
| Pass 3 additions: reconnect, staging/artifacts, queue/health, conflicts
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

                // Reconnect-triggered deferred replay for this node
                Route::post('/sync/reconnect', [TitanPwaController::class, 'reconnect'])->name('sync.reconnect');

                // Offline artifact staging
                Route::post('/staging/artifacts', [TitanPwaController::class, 'stageArtifacts'])->name('staging.artifacts');
                Route::get('/staging/status', [TitanPwaController::class, 'stagingStatus'])->name('staging.status');
            });

        // Operator diagnostics (auth-only, no throttle relaxation)
        Route::middleware(['auth'])
            ->prefix('diagnostics')
            ->as('diagnostics.')
            ->group(static function () {
                Route::get('/', [PwaDiagnosticsController::class, 'index'])->name('index');
                Route::get('/stats', [PwaDiagnosticsController::class, 'stats'])->name('stats');
                Route::get('/queue-health', [PwaDiagnosticsController::class, 'queueHealth'])->name('queue.health');
                Route::get('/conflicts', [PwaDiagnosticsController::class, 'conflicts'])->name('conflicts');
                Route::post('/nodes/promote', [PwaDiagnosticsController::class, 'promoteNode'])->name('nodes.promote');
                Route::post('/nodes/clear-rate-limit', [PwaDiagnosticsController::class, 'clearRateLimit'])->name('nodes.clear-rate-limit');
                Route::post('/replay', [PwaDiagnosticsController::class, 'triggerReplay'])->name('replay');
            });
    });
