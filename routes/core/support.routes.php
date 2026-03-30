<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:120,1'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        // TODO: migrate WorkCore support/communication routes (tickets/notices/knowledge) here.
    });
