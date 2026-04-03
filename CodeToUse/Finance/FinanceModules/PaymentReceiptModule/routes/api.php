<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/paymentreceiptmodule')->group(function() {
    Route::get('/', function() {
        return response()->json(['module' => 'paymentreceiptmodule', 'ok' => true]);
    });
});
