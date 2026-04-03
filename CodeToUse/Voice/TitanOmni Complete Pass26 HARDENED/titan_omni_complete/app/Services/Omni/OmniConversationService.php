<?php

namespace App\Services\Omni;

use App\Exceptions\OmniException;
use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniMessage;
use Illuminate\Support\Str;

/**
 * Enhanced OmniConversationService with comprehensive error handling and logging.
 * Provides idempotent conversation lifecycle management across all channels.
 */
class OmniConversationService
{
    public function __construct(
        protected \Psr\Log\LoggerInterface $logger
    ) {
    }

    /**
     * Find existing conversation or create new one (idempotent).
     *
     * @throws OmniException If company_id is invalid or validation fails
     */
    public function findOrCreate(array $attributes): OmniConversation
    {
        try {
            $this->validateConversationAttributes($attributes);

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

            if ($conversation->wasRecentlyCreated) {
                $this->logger->info('Conversation created', [
                    'conversation_id' => $conversation->id,
                    'uuid' => $conversation->uuid,
                    'company_id' => $conversation->company_id,
                    'channel_type' => $conversation->channel_type,
                ]);
            }

            return $conversation;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create/find conversation', [
                'attributes' => $this->sanitizeAttributes($attributes),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new OmniException(
                'Failed to create or retrieve conversation',
                0,
                $e,
                500,
                'CONVERSATION_CREATE_FAILED',
                ['attributes' => array_keys($attributes)]
            );
        }
    }

    /**
     * Append message to conversation with atomic counter updates.
     *
     * @throws OmniException If message creation fails or validation fails
     */
    public function appendMessage(OmniConversation $conversation, array $payload): OmniMessage
    {
        try {
            $this->validateMessagePayload($payload);

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
                'voice_confidence' => $this->validateConfidence($payload['voice_confidence'] ?? null),
                'media_url' => $payload['media_url'] ?? null,
                'media_type' => $payload['media_type'] ?? null,
                'media_size_bytes' => $this->validateMediaSize($payload['media_size_bytes'] ?? null),
                'external_message_id' => $payload['external_message_id'] ?? null,
                'is_internal_note' => $payload['is_internal_note'] ?? false,
                'metadata' => $payload['metadata'] ?? [],
                'created_at' => now(),
            ]);

            // Atomic counter updates
            $conversation->increment('total_messages');
            if (($payload['role'] ?? 'user') === 'user') {
                $conversation->increment('user_messages');
            } else {
                $conversation->increment('assistant_messages');
            }

            $conversation->forceFill(['last_activity_at' => now()])->save();

            $this->logger->debug('Message appended', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'role' => $message->role,
                'message_type' => $message->message_type,
            ]);

            return $message;
        } catch (\Exception $e) {
            $this->logger->error('Failed to append message', [
                'conversation_id' => $conversation->id,
                'payload_keys' => array_keys($payload),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new OmniException(
                'Failed to append message to conversation',
                0,
                $e,
                500,
                'MESSAGE_APPEND_FAILED',
                ['conversation_id' => $conversation->id]
            );
        }
    }

    /**
     * Validate conversation attributes before creation.
     *
     * @throws OmniException
     */
    protected function validateConversationAttributes(array $attributes): void
    {
        if (empty($attributes['company_id']) || !is_numeric($attributes['company_id'])) {
            throw new OmniException(
                'Invalid or missing company_id',
                0,
                null,
                400,
                'INVALID_COMPANY_ID',
                ['provided' => $attributes['company_id'] ?? 'null']
            );
        }

        if (empty($attributes['agent_id']) || !is_numeric($attributes['agent_id'])) {
            throw new OmniException(
                'Invalid or missing agent_id',
                0,
                null,
                400,
                'INVALID_AGENT_ID',
                ['provided' => $attributes['agent_id'] ?? 'null']
            );
        }

        if (!empty($attributes['customer_email'])) {
            if (!filter_var($attributes['customer_email'], FILTER_VALIDATE_EMAIL)) {
                throw new OmniException(
                    'Invalid customer email format',
                    0,
                    null,
                    400,
                    'INVALID_EMAIL',
                    ['email' => $attributes['customer_email']]
                );
            }
        }
    }

    /**
     * Validate message payload before creation.
     *
     * @throws OmniException
     */
    protected function validateMessagePayload(array $payload): void
    {
        if (empty($payload['content']) && empty($payload['voice_file_url']) && empty($payload['media_url'])) {
            throw new OmniException(
                'Message must have content, voice_file_url, or media_url',
                0,
                null,
                400,
                'EMPTY_MESSAGE',
                ['payload_keys' => array_keys($payload)]
            );
        }

        if (!empty($payload['role']) && !in_array($payload['role'], ['user', 'assistant', 'system'])) {
            throw new OmniException(
                'Invalid message role',
                0,
                null,
                400,
                'INVALID_ROLE',
                ['role' => $payload['role']]
            );
        }

        if (!empty($payload['message_type']) && !in_array($payload['message_type'], [
            'text', 'voice_transcript', 'voice_file', 'media', 'internal_note', 'system_event'
        ])) {
            throw new OmniException(
                'Invalid message type',
                0,
                null,
                400,
                'INVALID_MESSAGE_TYPE',
                ['message_type' => $payload['message_type']]
            );
        }
    }

    /**
     * Validate and clamp voice confidence to 0–1 range.
     */
    protected function validateConfidence(?float $confidence): ?float
    {
        if ($confidence === null) {
            return null;
        }

        if (!is_numeric($confidence)) {
            $this->logger->warning('Invalid confidence value', ['value' => $confidence]);
            return null;
        }

        return max(0, min(1, (float) $confidence));
    }

    /**
     * Validate media size to prevent DoS via huge attachments.
     */
    protected function validateMediaSize(?int $size): ?int
    {
        if ($size === null) {
            return null;
        }

        $maxBytes = config('omni.max_media_size_bytes', 100 * 1024 * 1024); // 100 MB default

        if ($size > $maxBytes) {
            throw new OmniException(
                "Media size exceeds limit: {$size} bytes > {$maxBytes} bytes",
                0,
                null,
                413,
                'MEDIA_SIZE_EXCEEDED',
                ['provided' => $size, 'max' => $maxBytes]
            );
        }

        return $size;
    }

    /**
     * Sanitize attributes for logging (remove sensitive data).
     */
    protected function sanitizeAttributes(array $attributes): array
    {
        $sanitized = $attributes;
        unset($sanitized['bridge_secret'], $sanitized['api_key'], $sanitized['token']);
        return $sanitized;
    }
}
