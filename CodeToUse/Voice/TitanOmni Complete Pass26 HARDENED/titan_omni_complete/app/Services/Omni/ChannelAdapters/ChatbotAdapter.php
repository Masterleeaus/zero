<?php

namespace App\Services\Omni\ChannelAdapters;

class ChatbotAdapter extends BaseOmniAdapter
{
    protected function driver(): string
    {
        return 'chatbot';
    }

    public function normalize(array $payload): array
    {
        return [
            'company_id' => $payload['company_id'] ?? null,
            'agent_id' => $payload['agent_id'] ?? null,
            'channel_type' => $payload['channel_type'] ?? 'web',
            'channel_id' => $payload['conversation_uuid'] ?? $payload['session_id'] ?? null,
            'session_id' => $payload['session_id'] ?? null,
            'customer_id' => $payload['customer_id'] ?? null,
            'customer_email' => $payload['customer_email'] ?? null,
            'customer_name' => $payload['customer_name'] ?? null,
            'role' => $payload['role'] ?? 'user',
            'message_type' => $payload['message_type'] ?? 'text',
            'content' => $payload['content'] ?? $payload['message'] ?? null,
            'external_message_id' => $payload['external_message_id'] ?? null,
            'legacy_ref' => $payload['conversation_uuid'] ?? null,
        ];
    }
}
