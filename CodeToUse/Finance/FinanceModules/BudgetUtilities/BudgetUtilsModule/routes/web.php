<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function() {
    Route::get('/budgetutilsmodule', function() {
        return view('budgetutilsmodule::index');
    })->name('budgetutilsmodule.index');
});
