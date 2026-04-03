<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/letter')->group(function() {
    Route::get('/', function() {
        return response()->json(['module' => 'letter', 'ok' => true]);
    });
});
