<?php

use Illuminate\Support\Facades\Route;
use Modules\ManagedPremises\Http\Controllers\Api\CalendarFeedController;

Route::middleware(['auth:sanctum'])
    ->prefix('sites')
    ->group(function () {
        Route::get('/calendar-feed', [CalendarFeedController::class, 'index'])->name('managedpremises.api.calendar_feed');
    });
