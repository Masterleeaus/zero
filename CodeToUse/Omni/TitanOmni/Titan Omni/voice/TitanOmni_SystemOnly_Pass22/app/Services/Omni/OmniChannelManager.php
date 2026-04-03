<?php

namespace App\Services\Omni;

class OmniChannelManager
{
    public function normalizeIncoming(string $channel, array $payload): array
    {
        return match ($channel) {
            'whatsapp', 'telegram', 'messenger', 'web', 'internal', 'api', 'voice' => [
                'channel' => $channel,
                'channel_id' => $payload['channel_id'] ?? $payload['session_id'] ?? null,
                'external_conversation_id' => $payload['external_conversation_id'] ?? null,
                'message' => $payload['message'] ?? null,
                'metadata' => $payload['metadata'] ?? [],
            ],
            default => [
                'channel' => 'web',
                'channel_id' => $payload['channel_id'] ?? null,
                'message' => $payload['message'] ?? null,
                'metadata' => $payload['metadata'] ?? [],
            ]
        };
    }
}
