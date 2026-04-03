<?php
use Illuminate\Support\Facades\Route;

// Apply PolicyGuard to API send + hooks
Route::middleware(['api', Modules\TitanTalk\Http\Middleware\PolicyGuard::class])->prefix('api/aiconverse')->group(function () {
    Route::post('/send', 'ConverseController@send')->name('titantalk.api.send'); // ensure exists
    Route::post('/hook/{driver}', 'WebhookController@receive')->name('titantalk.webhook.receive'); // ensure exists
});

// Analytics dashboard
Route::middleware(['web','auth'])->get('/titantalk/analytics', 'AnalyticsController@dashboard')->name('titantalk.analytics');
