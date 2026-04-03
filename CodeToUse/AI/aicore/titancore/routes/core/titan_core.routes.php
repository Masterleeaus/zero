<?php

use App\Http\Controllers\TitanCore\TitanCoreStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard/user/business-suite/core')
    ->as('dashboard.user.business-suite.core.')
    ->group(static function () {
        Route::get('/', [TitanCoreStatusController::class, 'index'])->name('index');
        Route::get('/status', [TitanCoreStatusController::class, 'api'])->name('status');
    });
