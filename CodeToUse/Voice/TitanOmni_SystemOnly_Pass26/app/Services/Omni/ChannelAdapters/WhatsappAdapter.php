<?php

namespace App\Services\Omni\ChannelAdapters;

class WhatsappAdapter extends BaseOmniAdapter
{
    protected function driver(): string
    {
        return 'whatsapp';
    }

    public function normalize(array $payload): array
    {
        return [
            'company_id' => $payload['company_id'] ?? null,
            'agent_id' => $payload['agent_id'] ?? null,
            'channel_type' => 'whatsapp',
            'channel_id' => $payload['from'] ?? $payload['wa_id'] ?? null,
            'session_id' => $payload['conversation_id'] ?? ($payload['from'] ?? null),
            'customer_name' => $payload['profile_name'] ?? null,
            'role' => $payload['role'] ?? 'user',
            'message_type' => $payload['message_type'] ?? 'text',
            'content' => $payload['body'] ?? $payload['message'] ?? null,
            'external_message_id' => $payload['message_sid'] ?? $payload['message_id'] ?? null,
            'legacy_ref' => $payload['conversation_id'] ?? null,
        ];
    }
}
