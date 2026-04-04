<?php

namespace App\Services\Omni\ChannelAdapters;

class TelegramAdapter extends BaseOmniAdapter
{
    protected function driver(): string
    {
        return 'telegram';
    }

    public function normalize(array $payload): array
    {
        return [
            'company_id' => $payload['company_id'] ?? null,
            'agent_id' => $payload['agent_id'] ?? null,
            'channel_type' => 'telegram',
            'channel_id' => (string) ($payload['chat_id'] ?? ''),
            'session_id' => (string) ($payload['chat_id'] ?? ''),
            'customer_name' => $payload['from_name'] ?? null,
            'role' => $payload['role'] ?? 'user',
            'message_type' => $payload['message_type'] ?? 'text',
            'content' => $payload['text'] ?? $payload['message'] ?? null,
            'external_message_id' => isset($payload['message_id']) ? (string) $payload['message_id'] : null,
            'legacy_ref' => isset($payload['update_id']) ? (string) $payload['update_id'] : null,
        ];
    }
}
