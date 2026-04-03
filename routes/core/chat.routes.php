<?php

use App\Http\Controllers\TitanCore\TitanChatBridgeController;
use Illuminate\Support\Facades\Route;

/**
 * Titan Chat Bridge Routes
 *
 * All chat surfaces (AIChatPro, Canvas, Chatbot, channel adapters) route
 * execution through OmniManager → TitanAIRouter → TitanMemory.
 *
 * These routes supplement — not replace — the existing extension routes
 * registered by AIChatProServiceProvider and CanvasServiceProvider.
 */
Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('api/titan/chat')
    ->as('titan.chat.')
    ->group(static function () {
        Route::post('/send', [TitanChatBridgeController::class, 'send'])->name('send');
        Route::get('/status', [TitanChatBridgeController::class, 'status'])->name('status');
    });
