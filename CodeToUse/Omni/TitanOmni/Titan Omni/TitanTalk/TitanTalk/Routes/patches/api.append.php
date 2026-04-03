<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Models\Conversation;
use Modules\TitanTalk\Models\Message;
use Modules\TitanTalk\Services\Providers\ProviderInterface;

/**
 * API addition used by the web widget / mobile apps.
 * NOTE: This file is loaded by the module RouteServiceProvider under the
 * global `/api` prefix.
 */
Route::middleware(['auth:api'])->prefix('aiconverse')->group(function () {
    Route::post('/send', function (Request $req, ProviderInterface $provider) {
        $text = (string) $req->input('message', '');
        $convId = $req->input('conversation_id');

        $conv = $convId ? Conversation::find($convId) : null;
        if (!$conv) {
            $conv = Conversation::create(['tenant_id' => null, 'channel' => 'web']);
        }

        Message::create([
            'tenant_id' => null,
            'conversation_id' => $conv->id,
            'sender' => 'user',
            'text' => $text,
            'meta' => [],
        ]);

        $resp = $provider->reply($text, ['conversation_id' => $conv->id]);

        Message::create([
            'tenant_id' => null,
            'conversation_id' => $conv->id,
            'sender' => 'bot',
            'text' => (string) ($resp['text'] ?? ''),
            'meta' => (array) ($resp['meta'] ?? []),
        ]);

        return response()->json([
            'conversation_id' => $conv->id,
            'reply' => $resp,
        ], 200);
    })->name('titantalk.send');
});
