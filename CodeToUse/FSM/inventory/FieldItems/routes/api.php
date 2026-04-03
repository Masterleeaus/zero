<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/items')->group(function() {
    Route::get('/', function() {
        return response()->json(['module' => 'items', 'ok' => true]);
    });
});
