<?php

namespace App\TitanCore\Omni;

use App\TitanCore\Zero\AI\TitanAIRouter;
use App\TitanCore\Zero\Telemetry\TelemetryManager;
use Illuminate\Support\Str;

class OmniManager
{
    public function __construct(
        protected TelemetryManager $telemetryManager,
        protected TitanAIRouter $router,
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
}
