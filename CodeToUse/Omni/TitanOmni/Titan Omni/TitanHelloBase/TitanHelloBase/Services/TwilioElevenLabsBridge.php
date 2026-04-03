<?php

declare(strict_types=1);

namespace Extensions\TitanHello\Services;

use Extensions\TitanHello\Models\TitanHelloCallEvent;
use Extensions\TitanHello\Models\TitanHelloCallSession;
use Illuminate\Support\Facades\Log;

/**
 * Twilio ↔ ElevenLabs realtime bridge (Path A)
 *
 * IMPORTANT:
 * - Twilio Media Streams require a WSS WebSocket server.
 * - Traditional Laravel HTTP routes do NOT serve WebSockets.
 * - This class contains ONLY the per-call bridging logic; the WebSocket server wrapper
 *   is provided in /bridge/titan_hello_ws_server.php.
 *
 * The WS server:
 * 1) accepts Twilio JSON frames: start / media / stop
 * 2) attaches to the relevant CallSession
 * 3) forwards audio to ElevenLabs agent WS (to be implemented once your ElevenLabsService
 *    exposes a realtime WS client endpoint)
 * 4) forwards agent audio back to Twilio
 */
class TwilioElevenLabsBridge
{
    public function __construct(
        protected SettingsService $settings,
        protected ElevenLabsAgentService $eleven,
    ) {}

    /**
     * Handle Twilio "start" event.
     */
    public function onStart(array $payload): void
    {
        $callSid = (string) ($payload['start']['callSid'] ?? '');
        if ($callSid === '') {
            return;
        }

        $session = TitanHelloCallSession::query()->where('call_sid', $callSid)->first();
        if (!$session) {
            return;
        }

        TitanHelloCallEvent::query()->create([
            'call_session_id' => $session->id,
            'type' => 'stream_start',
            'payload' => $payload,
        ]);
    }

    /**
     * Handle Twilio "media" event.
     *
     * Payload includes base64 audio in payload['media']['payload'].
     */
    public function onMedia(array $payload): void
    {
        // TODO (Step 3+): forward audio to ElevenLabs realtime agent session.
        // For now we only log periodic markers to avoid massive DB writes.
        $callSid = (string) ($payload['streamSid'] ?? $payload['media']['track'] ?? '');
        if ($callSid === '') {
            return;
        }

        // Intentionally no DB write per chunk.
    }

    /**
     * Handle Twilio "stop" event.
     */
    public function onStop(array $payload): void
    {
        $callSid = (string) ($payload['stop']['callSid'] ?? '');
        if ($callSid === '') {
            return;
        }

        $session = TitanHelloCallSession::query()->where('call_sid', $callSid)->first();
        if (!$session) {
            return;
        }

        TitanHelloCallEvent::query()->create([
            'call_session_id' => $session->id,
            'type' => 'stream_stop',
            'payload' => $payload,
        ]);

        $session->status = 'completed';
        $session->ended_at = now();
        $session->save();
    }
}
