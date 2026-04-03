<?php

namespace App\TitanCore\Chat\Channels;

use App\TitanCore\Chat\Contracts\ChannelAdapterContract;
use Illuminate\Support\Str;

/**
 * ExternalChatbotChannelAdapter
 *
 * Adapter for the external/embedded chatbot widget (External-Chatbot extension).
 * Used when the chatbot is embedded in a third-party website or system.
 *
 * Source: CodeToUse/Extensions/ExtensionLibrary/External-Chatbot
 */
class ExternalChatbotChannelAdapter implements ChannelAdapterContract
{
    public function channel(): string
    {
        return 'external';
    }

    public function toEnvelope(array $payload): array
    {
        return [
            'id'         => (string) Str::uuid(),
            'intent'     => 'chat.complete',
            'stage'      => 'suggestion',
            'channel'    => $this->channel(),
            'surface'    => 'external_chatbot',
            'input'      => $payload['message'] ?? $payload['input'] ?? '',
            'company_id' => $payload['company_id'] ?? null,
            'user_id'    => $payload['user_id'] ?? null,
            'session_id' => $payload['session_id'] ?? (string) Str::uuid(),
            'meta'       => [
                'chatbot_id'  => $payload['chatbot_id'] ?? null,
                'origin_url'  => $payload['origin_url'] ?? null,
                'visitor_id'  => $payload['visitor_id'] ?? null,
                'embed_token' => $payload['embed_token'] ?? null,
            ],
        ];
    }

    public function sendResponse(array $result, array $context): void
    {
        // External chatbot responses are returned as JSON by the embedding API endpoint.
        // The External-Chatbot extension controller handles the HTTP response.
    }
}
