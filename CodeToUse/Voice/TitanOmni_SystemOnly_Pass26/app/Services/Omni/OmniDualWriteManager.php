<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniConversation;

class OmniDualWriteManager
{
    public function __construct(
        protected OmniConversationService $conversations
    ) {
    }

    public function ingest(array $payload): array
    {
        $conversation = $this->conversations->findOrCreate([
            'company_id' => $payload['company_id'],
            'agent_id' => $payload['agent_id'],
            'channel_type' => $payload['channel_type'] ?? 'web',
            'channel_id' => $payload['channel_id'] ?? ($payload['session_id'] ?? null),
            'session_id' => $payload['session_id'] ?? null,
            'customer_id' => $payload['customer_id'] ?? null,
            'customer_email' => $payload['customer_email'] ?? null,
            'customer_name' => $payload['customer_name'] ?? null,
            'metadata' => $payload['conversation_metadata'] ?? [],
        ]);

        $message = $this->conversations->appendMessage($conversation, [
            'agent_id' => $payload['agent_id'] ?? null,
            'role' => $payload['role'] ?? 'user',
            'message_type' => $payload['message_type'] ?? 'text',
            'content' => $payload['content'] ?? null,
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
            'metadata' => $payload['message_metadata'] ?? [],
        ]);

        return [
            'conversation' => $conversation,
            'message' => $message,
        ];
    }
}
