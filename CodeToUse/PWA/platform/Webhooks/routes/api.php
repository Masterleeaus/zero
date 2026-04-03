<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/webhooks')->group(function() {
    Route::get('/', function() {
        return response()->json(['module' => 'webhooks', 'ok' => true]);
    });
});
