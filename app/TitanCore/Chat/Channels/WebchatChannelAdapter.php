<?php

namespace App\TitanCore\Chat\Channels;

use App\TitanCore\Chat\Contracts\ChannelAdapterContract;
use Illuminate\Support\Str;

/**
 * WebchatChannelAdapter
 *
 * Adapter for embedded webchat widget (Webchat extension).
 * Handles browser-embedded chat sessions on external web pages.
 *
 * Source: CodeToUse/Extensions/ExtensionLibrary/Webchat
 */
class WebchatChannelAdapter implements ChannelAdapterContract
{
    public function channel(): string
    {
        return 'webchat';
    }

    public function toEnvelope(array $payload): array
    {
        return [
            'id'         => (string) Str::uuid(),
            'intent'     => 'chat.complete',
            'stage'      => 'suggestion',
            'channel'    => $this->channel(),
            'surface'    => 'webchat',
            'input'      => $payload['message'] ?? $payload['input'] ?? '',
            'company_id' => $payload['company_id'] ?? null,
            'user_id'    => $payload['user_id'] ?? null,
            'session_id' => $payload['session_id'] ?? (string) Str::uuid(),
            'meta'       => [
                'widget_id'  => $payload['widget_id'] ?? null,
                'page_url'   => $payload['page_url'] ?? null,
                'visitor_id' => $payload['visitor_id'] ?? null,
            ],
        ];
    }

    public function sendResponse(array $result, array $context): void
    {
        // Webchat responses are streamed back via the HTTP response / SSE.
        // The Webchat extension controller handles the actual streaming.
    }
}
