<?php

namespace Modules\TitanHello\Services\Providers\Twilio;

class TwilioWebhookMapper
{
    public function inbound(array $p): array
    {
        return [
            'direction' => 'inbound',
            'provider' => 'twilio',
            'provider_call_sid' => $p['CallSid'] ?? null,
            'from_number' => $p['From'] ?? null,
            'to_number' => $p['To'] ?? null,
            'status' => 'ringing',
            'recording_enabled' => true,
        ];
    }

    public function status(array $p): array
    {
        $status = $p['CallStatus'] ?? null;
        return [
            'provider_call_sid' => $p['CallSid'] ?? null,
            'status' => $status ?: null,
            'duration_seconds' => isset($p['CallDuration']) ? (int) $p['CallDuration'] : null,
        ];
    }

    public function recording(array $p): array
    {
        return [
            'provider_call_sid' => $p['CallSid'] ?? null,
            'provider_recording_sid' => $p['RecordingSid'] ?? null,
            'kind' => (($p['RecordingSource'] ?? '') === 'RecordVerb') ? 'voicemail' : 'call',
            'recording_url' => $p['RecordingUrl'] ?? null,
            'duration_seconds' => isset($p['RecordingDuration']) ? (int) $p['RecordingDuration'] : null,
            'content_type' => null,
            'stored_path' => null,
            'available_at' => now(),
            'provider' => 'twilio',
        ];
    }
}
