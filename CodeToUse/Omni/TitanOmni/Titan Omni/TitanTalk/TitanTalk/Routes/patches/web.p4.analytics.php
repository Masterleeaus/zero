<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Http\Controllers\AnalyticsController;

// Analytics dashboard (web)
Route::middleware(['web','auth'])
    ->get('/titantalk/analytics', [AnalyticsController::class, 'dashboard'])
    ->name('titantalk.analytics');
