<?php

declare(strict_types=1);

namespace App\Services\Omni;

use App\Events\Omni\OmniConversationResolved;
use App\Events\Omni\OmniConversationStarted;
use App\Events\Omni\OmniConversationTransferred;
use App\Events\Omni\OmniMessageReceived;
use App\Events\Omni\OmniMessageSent;
use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OmniConversationService — conversation lifecycle management.
 *
 * Provides idempotent create/resolve/transfer and message persistence
 * for all Omni channels. All writes are scoped by company_id.
 */
class OmniConversationService
{
    /**
     * Find an existing open conversation by channel identity, or create a new one.
     * Idempotent: safe to call on every inbound message.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function findOrCreate(array $attributes): OmniConversation
    {
        $this->validateConversationAttributes($attributes);

        $match = [
            'company_id'   => $attributes['company_id'],
            'agent_id'     => $attributes['agent_id'],
            'channel_type' => $attributes['channel_type'] ?? 'web',
            'channel_id'   => $attributes['channel_id'] ?? ($attributes['session_id'] ?? null),
        ];

        $conversation = OmniConversation::withoutGlobalScope('company')
            ->where('status', 'open')
            ->where($match)
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $conversation = OmniConversation::create(array_merge($match, [
            'uuid'                     => (string) Str::uuid(),
            'omni_customer_id'         => $attributes['omni_customer_id'] ?? null,
            'crm_customer_id'          => $attributes['crm_customer_id'] ?? null,
            'customer_name'            => $attributes['customer_name'] ?? null,
            'customer_email'           => $attributes['customer_email'] ?? null,
            'session_id'               => $attributes['session_id'] ?? null,
            'external_conversation_id' => $attributes['external_conversation_id'] ?? null,
            'status'                   => 'open',
            'last_activity_at'         => now(),
            'metadata'                 => $attributes['metadata'] ?? null,
        ]));

        event(new OmniConversationStarted($conversation));

        Log::info('omni.conversation.started', [
            'conversation_id' => $conversation->id,
            'uuid'            => $conversation->uuid,
            'company_id'      => $conversation->company_id,
            'channel_type'    => $conversation->channel_type,
        ]);

        return $conversation;
    }

    /**
     * Append a message to a conversation and update last_activity_at.
     *
     * @param  array<string, mixed>  $payload
     */
    public function appendMessage(OmniConversation $conversation, array $payload): OmniMessage
    {
        $message = OmniMessage::create([
            'uuid'            => (string) Str::uuid(),
            'conversation_id' => $conversation->id,
            'company_id'      => $conversation->company_id,
            'agent_id'        => $payload['agent_id'] ?? $conversation->agent_id,
            'direction'       => $payload['direction'] ?? 'inbound',
            'content_type'    => $payload['content_type'] ?? 'text',
            'content'         => $payload['content'] ?? null,
            'sender_type'     => $payload['sender_type'] ?? 'customer',
            'sender_id'       => $payload['sender_id'] ?? null,
            'media_url'       => $payload['media_url'] ?? null,
            'media_type'      => $payload['media_type'] ?? null,
            'voice_file_url'  => $payload['voice_file_url'] ?? null,
            'voice_transcript' => $payload['voice_transcript'] ?? null,
            'external_message_id' => $payload['external_message_id'] ?? null,
            'is_internal_note' => $payload['is_internal_note'] ?? false,
            'metadata'        => $payload['metadata'] ?? null,
            'created_at'      => now(),
        ]);

        $conversation->increment('total_messages');
        $conversation->update(['last_activity_at' => now()]);

        $event = ($message->direction === 'outbound')
            ? new OmniMessageSent($message)
            : new OmniMessageReceived($message);

        event($event);

        return $message;
    }

    /**
     * Resolve (close) a conversation. Sets resolved_at once — immutable.
     */
    public function resolve(OmniConversation $conversation, ?int $resolvedBy = null): OmniConversation
    {
        if ($conversation->status === 'resolved') {
            return $conversation;
        }

        $conversation->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);

        event(new OmniConversationResolved($conversation));

        Log::info('omni.conversation.resolved', [
            'conversation_id' => $conversation->id,
            'resolved_by'     => $resolvedBy,
        ]);

        return $conversation->fresh();
    }

    /**
     * Transfer a conversation to a different agent / user.
     */
    public function transfer(OmniConversation $conversation, int $newAgentUserId, ?int $transferredBy = null): OmniConversation
    {
        $conversation->update(['assigned_to' => $newAgentUserId]);

        event(new OmniConversationTransferred($conversation));

        Log::info('omni.conversation.transferred', [
            'conversation_id' => $conversation->id,
            'new_assigned_to' => $newAgentUserId,
            'transferred_by'  => $transferredBy,
        ]);

        return $conversation->fresh();
    }

    // ── Validation helpers ────────────────────────────────────────────────────

    private function validateConversationAttributes(array $attributes): void
    {
        if (empty($attributes['company_id'])) {
            throw new \InvalidArgumentException('company_id is required to create an OmniConversation.');
        }
        if (empty($attributes['agent_id'])) {
            throw new \InvalidArgumentException('agent_id is required to create an OmniConversation.');
        }
    }
}
