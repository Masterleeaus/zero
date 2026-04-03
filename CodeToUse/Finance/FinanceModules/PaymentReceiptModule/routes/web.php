<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function() {
    Route::get('/paymentreceiptmodule', function() {
        return view('paymentreceiptmodule::index');
    })->name('paymentreceiptmodule.index');
});
