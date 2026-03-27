<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('team')->as('team.')->group(static function () {
            Route::get('roster', [\App\Http\Controllers\Team\TeamController::class, 'index'])
                ->name('roster.index');
        });
    });
