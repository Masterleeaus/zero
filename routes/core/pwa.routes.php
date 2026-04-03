<?php

use App\Http\Controllers\TitanPwa\TitanPwaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Titan Zero PWA Routes
|--------------------------------------------------------------------------
|
| Public endpoints: manifest, bootstrap
| Auth-guarded: handshake, signals/ingest, sync/status
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
    });
