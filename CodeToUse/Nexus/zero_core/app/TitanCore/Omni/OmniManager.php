<?php

namespace App\TitanCore\Omni;

use App\TitanCore\Zero\Telemetry\TelemetryManager;

class OmniManager
{
    public function __construct(
        protected TelemetryManager $telemetryManager,
    ) {
    }

    /**
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
}
