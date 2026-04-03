<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanHello\Http\Controllers\TitanHelloController;

Route::get('/', [TitanHelloController::class, 'index'])->name('index');
Route::get('/conversations', [TitanHelloController::class, 'conversations'])->name('conversations');
Route::get('/settings', [TitanHelloController::class, 'settings'])->name('settings');
