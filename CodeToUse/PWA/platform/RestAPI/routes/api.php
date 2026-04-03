<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/restapi')->group(function() {
    Route::get('/', function() {
        return response()->json(['module' => 'restapi', 'ok' => true]);
    });
});
