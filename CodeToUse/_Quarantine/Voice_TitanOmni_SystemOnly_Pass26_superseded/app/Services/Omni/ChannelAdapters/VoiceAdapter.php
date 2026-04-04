<?php

namespace App\Services\Omni\ChannelAdapters;

class VoiceAdapter extends BaseOmniAdapter
{
    protected function driver(): string
    {
        return 'voice';
    }

    public function normalize(array $payload): array
    {
        return [
            'company_id' => $payload['company_id'] ?? null,
            'agent_id' => $payload['agent_id'] ?? null,
            'channel_type' => 'voice',
            'channel_id' => $payload['call_sid'] ?? null,
            'session_id' => $payload['from_number'] ?? null,
            'customer_name' => $payload['customer_name'] ?? null,
            'role' => $payload['role'] ?? 'user',
            'message_type' => $payload['message_type'] ?? 'voice_transcript',
            'content' => $payload['transcript'] ?? $payload['message'] ?? null,
            'voice_file_url' => $payload['recording_url'] ?? null,
            'voice_duration_seconds' => $payload['duration_seconds'] ?? null,
            'voice_model' => $payload['voice_model'] ?? null,
            'voice_transcript' => $payload['transcript'] ?? null,
            'voice_confidence' => $payload['confidence'] ?? null,
            'external_message_id' => $payload['call_sid'] ?? null,
            'legacy_ref' => $payload['call_sid'] ?? null,
        ];
    }
}
