<?php

declare(strict_types=1);

use App\Http\Controllers\Trust\TrustLedgerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('throttle.dashboard', '120,1')])
    ->prefix('dashboard/trust')
    ->as('dashboard.trust.')
    ->group(static function () {
        Route::get('chain',  [TrustLedgerController::class, 'chain'])->name('chain');
        Route::get('verify', [TrustLedgerController::class, 'verify'])->name('verify');
        Route::get('proof',  [TrustLedgerController::class, 'proof'])->name('proof');
        Route::post('seal',  [TrustLedgerController::class, 'seal'])->name('seal');
    });
