<?php

namespace App\Services\Omni;

class OmniLegacyConversationMirror
{
    public function mapLegacyPayload(string $driver, array $payload): array
    {
        return match ($driver) {
            'chatbot', 'whatsapp', 'telegram', 'messenger', 'voice' => [
                'company_id' => $payload['company_id'] ?? null,
                'agent_id' => $payload['agent_id'] ?? null,
                'channel_type' => $payload['channel_type'] ?? $driver,
                'channel_id' => $payload['channel_id'] ?? ($payload['session_id'] ?? null),
                'session_id' => $payload['session_id'] ?? null,
                'customer_id' => $payload['customer_id'] ?? null,
                'customer_email' => $payload['customer_email'] ?? null,
                'customer_name' => $payload['customer_name'] ?? null,
                'role' => $payload['role'] ?? 'user',
                'message_type' => $payload['message_type'] ?? 'text',
                'content' => $payload['content'] ?? null,
                'external_message_id' => $payload['external_message_id'] ?? null,
                'message_metadata' => array_merge($payload['message_metadata'] ?? [], [
                    'legacy_driver' => $driver,
                    'legacy_ref' => $payload['legacy_ref'] ?? null,
                ]),
                'conversation_metadata' => array_merge($payload['conversation_metadata'] ?? [], [
                    'legacy_driver' => $driver,
                ]),
            ],
            default => $payload,
        };
    }
}
