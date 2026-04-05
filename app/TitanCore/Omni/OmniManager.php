<?php

namespace App\TitanCore\Omni;

use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniMessage;
use App\Services\Omni\OmniConversationService;
use App\TitanCore\Zero\AI\TitanAIRouter;
use App\TitanCore\Zero\Telemetry\TelemetryManager;
use Illuminate\Support\Str;

class OmniManager
{
    public function __construct(
        protected TelemetryManager $telemetryManager,
        protected TitanAIRouter $router,
        protected ?OmniConversationService $conversationService = null,
    ) {
    }

    /**
     * Ingest an incoming channel message and record telemetry.
     *
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>
     */
    public function ingest(array $message): array
    {
        $payload = [
            'status' => 'captured',
            'channel' => $message['channel'] ?? 'workspace',
            'message_id' => $message['id'] ?? null,
            'captured_at' => now()->toIso8601String(),
        ];

        $this->telemetryManager->record('omni.message.ingested', $payload);

        return $payload;
    }

    /**
     * Dispatch a chat message from any surface through the canonical Titan AI pipeline.
     *
     * All surfaces (Chatbot, AIChatPro, Canvas, channel adapters) MUST route through here.
     * Pipeline: OmniManager → TitanAIRouter → TitanMemory → Signal/Approval/Rewind
     *
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    public function dispatch(array $envelope): array
    {
        $envelope = $this->normaliseEnvelope($envelope);

        $this->telemetryManager->record('omni.message.dispatching', [
            'surface'    => $envelope['surface'] ?? 'unknown',
            'channel'    => $envelope['channel'] ?? 'workspace',
            'session_id' => $envelope['session_id'] ?? null,
            'company_id' => $envelope['company_id'] ?? null,
        ]);

        $result = $this->router->execute($envelope);

        $this->telemetryManager->record('omni.message.dispatched', [
            'surface'    => $envelope['surface'] ?? 'unknown',
            'channel'    => $envelope['channel'] ?? 'workspace',
            'session_id' => $envelope['session_id'] ?? null,
            'status'     => $result['status'] ?? 'ok',
        ]);

        return $result;
    }

    /**
     * Normalise an inbound chat envelope to the Titan standard format.
     *
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    protected function normaliseEnvelope(array $envelope): array
    {
        $envelope['id']         = $envelope['id'] ?? (string) Str::uuid();
        $envelope['intent']     = $envelope['intent'] ?? 'chat.complete';
        $envelope['stage']      = $envelope['stage'] ?? 'suggestion';
        $envelope['channel']    = $envelope['channel'] ?? 'workspace';
        $envelope['surface']    = $envelope['surface'] ?? 'chat';
        $envelope['company_id'] = $envelope['company_id'] ?? ($envelope['team_id'] ?? null);

        return $envelope;
    }

    /**
     * Persist or retrieve the OmniConversation for the given envelope.
     *
     * Delegates to OmniConversationService::findOrCreate() when the service
     * is available (injected in the container). Falls back to a no-op null
     * return when running outside the Omni context (e.g. workspace chat).
     *
     * @param  array<string, mixed>  $envelope  Normalised dispatch envelope
     */
    public function persistConversation(array $envelope): ?OmniConversation
    {
        if (! $this->conversationService) {
            return null;
        }

        $companyId = $envelope['company_id'] ?? null;
        $agentId   = $envelope['agent_id'] ?? null;

        if (! $companyId || ! $agentId) {
            return null;
        }

        return $this->conversationService->findOrCreate([
            'company_id'               => $companyId,
            'agent_id'                 => $agentId,
            'channel_type'             => $envelope['channel'] ?? 'web',
            'channel_id'               => $envelope['channel_id'] ?? ($envelope['session_id'] ?? null),
            'session_id'               => $envelope['session_id'] ?? null,
            'external_conversation_id' => $envelope['external_conversation_id'] ?? null,
            'omni_customer_id'         => $envelope['omni_customer_id'] ?? null,
            'crm_customer_id'          => $envelope['crm_customer_id'] ?? null,
            'customer_name'            => $envelope['customer_name'] ?? null,
            'customer_email'           => $envelope['customer_email'] ?? null,
            'metadata'                 => $envelope['metadata'] ?? null,
        ]);
    }

    /**
     * Persist an OmniMessage against an existing OmniConversation.
     *
     * @param  array<string, mixed>  $envelope  Normalised dispatch envelope
     */
    public function persistMessage(OmniConversation $conversation, array $envelope): ?OmniMessage
    {
        if (! $this->conversationService) {
            return null;
        }

        return $this->conversationService->appendMessage($conversation, [
            'agent_id'            => $envelope['agent_id'] ?? $conversation->agent_id,
            'direction'           => $envelope['direction'] ?? 'inbound',
            'content_type'        => $envelope['content_type'] ?? 'text',
            'content'             => $envelope['content'] ?? ($envelope['message'] ?? null),
            'sender_type'         => $envelope['sender_type'] ?? 'customer',
            'sender_id'           => $envelope['sender_id'] ?? null,
            'media_url'           => $envelope['media_url'] ?? null,
            'media_type'          => $envelope['media_type'] ?? null,
            'voice_file_url'      => $envelope['voice_file_url'] ?? null,
            'voice_transcript'    => $envelope['voice_transcript'] ?? null,
            'external_message_id' => $envelope['external_message_id'] ?? null,
            'is_internal_note'    => $envelope['is_internal_note'] ?? false,
            'metadata'            => $envelope['metadata'] ?? null,
        ]);
    }
}
