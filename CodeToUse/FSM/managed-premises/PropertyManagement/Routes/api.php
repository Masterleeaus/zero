<?php

use Illuminate\Support\Facades\Route;
use Modules\PropertyManagement\Http\Controllers\Api\CalendarFeedController;

Route::middleware(['auth:sanctum'])
    ->prefix('property-management')
    ->group(function () {
        Route::get('/calendar-feed', [CalendarFeedController::class, 'index'])->name('propertymanagement.api.calendar_feed');
    });
