<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function() {
    Route::get('/restapi', function() {
        return view('restapi::index');
    })->name('restapi.index');
});
