<?php

use App\Http\Controllers\TitanCore\TitanCoreStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:120,1'])
    ->prefix('dashboard/user/business-suite/core')
    ->as('titan.core.')
    ->group(static function () {
        Route::get('/', [TitanCoreStatusController::class, 'index'])->name('status');
        Route::get('/health', [TitanCoreStatusController::class, 'api'])->name('health');
        Route::get('/runtime', [TitanCoreStatusController::class, 'runtime'])->name('runtime');
    });
