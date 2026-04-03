<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Http\Controllers\TitanTalkController;

Route::get('/', [TitanTalkController::class, 'index'])->name('index');
Route::get('/conversations', [TitanTalkController::class, 'conversations'])->name('conversations');
Route::get('/settings', [TitanTalkController::class, 'settings'])->name('settings');
