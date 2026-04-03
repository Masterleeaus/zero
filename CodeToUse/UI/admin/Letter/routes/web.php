<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function() {
    Route::get('/letter', function() {
        return view('letter::index');
    })->name('letter.index');
});
