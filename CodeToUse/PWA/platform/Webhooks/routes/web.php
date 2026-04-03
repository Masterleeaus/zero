<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function() {
    Route::get('/webhooks', function() {
        return view('webhooks::index');
    })->name('webhooks.index');
});
