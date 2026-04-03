<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/budgetutilsmodule')->group(function() {
    Route::get('/', function() {
        return response()->json(['module' => 'budgetutilsmodule', 'ok' => true]);
    });
});
