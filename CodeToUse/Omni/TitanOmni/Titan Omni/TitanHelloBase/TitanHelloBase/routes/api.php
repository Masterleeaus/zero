<?php

use Illuminate\Support\Facades\Route;
use Extensions\TitanHello\Controllers\ChatbotVoiceEmbbedController;
use Extensions\TitanHello\Controllers\ChatbotVoiceHistoryController;
use Extensions\TitanHello\Controllers\TwilioWebhookController;
use Extensions\TitanHello\Controllers\InternalEventsController;
use Extensions\TitanHello\Middleware\VerifyTwilioSignature;

// Public-ish API used by the voice runtime (kept for compatibility). Add throttling/signatures in hardening pass.
Route::prefix('api/v2/titan-hello')->name('api.v2.titan-hello.')->group(function () {
    Route::get('/{uuid}', [ChatbotVoiceEmbbedController::class, 'index'])->name('agent');
    Route::post('/{uuid}/store-conversation', [ChatbotVoiceHistoryController::class, 'storeConversation'])->name('store-conversation');
});

// Twilio Voice Webhooks (Phone Answering)
Route::prefix('api/titan-hello/twilio/voice')
    ->middleware([VerifyTwilioSignature::class, 'throttle:120,1'])
    ->name('api.titan-hello.twilio.voice.')
    ->group(function () {
    Route::post('/inbound', [TwilioWebhookController::class, 'inbound'])->name('inbound');
    Route::post('/status', [TwilioWebhookController::class, 'status'])->name('status');
    Route::post('/recording', [TwilioWebhookController::class, 'recording'])->name('recording');
    // Media Streams WebSocket URL (Twilio connects via ws/wss).
    // Laravel cannot serve WebSockets directly; terminate TLS + proxy this path
    // to the standalone bridge shipped at:
    //   extensions/titan-hello/bridge/titan_hello_ws_server.php
    Route::match(['GET','POST'], '/stream', function () {
        return response('Titan Hello: WebSocket endpoint. Configure proxy to bridge daemon.', 426);
    })->name('stream');
});


// Internal bridge callbacks (protected by shared secret)
Route::prefix('api/titan-hello/internal')->middleware(['throttle:240,1'])->group(function () {
    Route::post('/transcript', [InternalEventsController::class, 'transcript']);
    Route::post('/lead', [InternalEventsController::class, 'lead']);
});
