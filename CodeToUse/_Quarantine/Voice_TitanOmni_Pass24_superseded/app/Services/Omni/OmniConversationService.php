<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniMessage;
use Illuminate\Support\Str;

class OmniConversationService
{
    public function findOrCreate(array $attributes): OmniConversation
    {
        $match = [
            'company_id' => $attributes['company_id'],
            'agent_id' => $attributes['agent_id'],
            'channel_type' => $attributes['channel_type'] ?? 'web',
            'channel_id' => $attributes['channel_id'] ?? ($attributes['session_id'] ?? null),
        ];

        $conversation = OmniConversation::query()->firstOrCreate(
            $match,
            [
                'uuid' => (string) Str::uuid(),
                'customer_id' => $attributes['customer_id'] ?? null,
                'customer_email' => $attributes['customer_email'] ?? null,
                'customer_name' => $attributes['customer_name'] ?? null,
                'session_id' => $attributes['session_id'] ?? null,
                'status' => 'open',
                'last_activity_at' => now(),
                'metadata' => $attributes['metadata'] ?? [],
            ]
        );

        return $conversation;
    }

    public function appendMessage(OmniConversation $conversation, array $payload): OmniMessage
    {
        $message = $conversation->messages()->create([
            'uuid' => (string) Str::uuid(),
            'agent_id' => $payload['agent_id'] ?? null,
            'message_type' => $payload['message_type'] ?? 'text',
            'content' => $payload['content'] ?? null,
            'role' => $payload['role'] ?? 'user',
            'voice_file_url' => $payload['voice_file_url'] ?? null,
            'voice_duration_seconds' => $payload['voice_duration_seconds'] ?? null,
            'voice_model' => $payload['voice_model'] ?? null,
            'voice_transcript' => $payload['voice_transcript'] ?? null,
            'voice_confidence' => $payload['voice_confidence'] ?? null,
            'media_url' => $payload['media_url'] ?? null,
            'media_type' => $payload['media_type'] ?? null,
            'media_size_bytes' => $payload['media_size_bytes'] ?? null,
            'external_message_id' => $payload['external_message_id'] ?? null,
            'is_internal_note' => $payload['is_internal_note'] ?? false,
            'metadata' => $payload['metadata'] ?? [],
            'created_at' => now(),
        ]);

        $conversation->increment('total_messages');
        if (($payload['role'] ?? 'user') === 'user') {
            $conversation->increment('user_messages');
        } else {
            $conversation->increment('assistant_messages');
        }

        $conversation->forceFill(['last_activity_at' => now()])->save();

        return $message;
    }
}
