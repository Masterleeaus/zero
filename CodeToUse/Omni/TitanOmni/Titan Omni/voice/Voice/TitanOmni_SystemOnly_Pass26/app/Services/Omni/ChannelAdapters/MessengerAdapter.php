<?php

namespace App\Services\Omni\ChannelAdapters;

class MessengerAdapter extends BaseOmniAdapter
{
    protected function driver(): string
    {
        return 'messenger';
    }

    public function normalize(array $payload): array
    {
        return [
            'company_id' => $payload['company_id'] ?? null,
            'agent_id' => $payload['agent_id'] ?? null,
            'channel_type' => 'messenger',
            'channel_id' => $payload['sender_id'] ?? null,
            'session_id' => $payload['sender_id'] ?? null,
            'customer_name' => $payload['sender_name'] ?? null,
            'role' => $payload['role'] ?? 'user',
            'message_type' => $payload['message_type'] ?? 'text',
            'content' => $payload['text'] ?? $payload['message'] ?? null,
            'external_message_id' => $payload['mid'] ?? null,
            'legacy_ref' => $payload['thread_id'] ?? null,
        ];
    }
}
