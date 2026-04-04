<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\Chatbot\App\Http\Controllers\API\ChannelBridgeHealthController;
use App\Extensions\Chatbot\App\Http\Controllers\API\ChannelIngressController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard/chatbot/bridges/health', [ChannelBridgeHealthController::class, 'index'])
        ->name('dashboard.user.chatbot.bridges.health');
});

Route::middleware(['web'])->group(function () {
    Route::post('/api/v1/chatbot/bridges/{channel}/ingest', [ChannelIngressController::class, 'ingest'])
        ->name('api.v1.chatbot.bridges.ingest');
});
