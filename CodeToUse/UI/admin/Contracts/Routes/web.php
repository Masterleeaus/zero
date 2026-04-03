<?php

use Illuminate\Support\Facades\Route;
use Modules\Contracts\Http\Controllers\ContractController;

// Admin (auth)
Route::middleware(['web','auth'])->group(function(){
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{id}', [ContractController::class, 'show'])->name('contracts.show');
    Route::post('/contracts/{id}/send', [ContractController::class, 'send'])->name('contracts.send');
});

// Public (signed)
Route::middleware(['web','signed'])->group(function(){
    Route::get('/contracts/public/{id}', [ContractController::class, 'publicShow'])->name('contracts.public.show');
    Route::post('/contracts/public/{id}/sign', [ContractController::class, 'sign'])->name('contracts.public.sign');
    Route::post('/contracts/public/{id}/decline', [ContractController::class, 'decline'])->name('contracts.public.decline');
});
