<?php
use Illuminate\Support\Facades\Route;
use Modules\FixedAssets\Http\Middleware\FixedAssetsAiQuota;

Route::middleware(['web','auth','can:fixedassets.access', FixedAssetsAiQuota::class])
    ->prefix('fixedassets/ai')->group(function () {
        Route::post('/categorize', fn() => response()->json(['ok'=>true]));
    });
