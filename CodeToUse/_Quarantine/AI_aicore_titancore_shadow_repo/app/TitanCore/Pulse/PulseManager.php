<?php

namespace App\TitanCore\Pulse;

use App\TitanCore\Zero\Telemetry\TelemetryManager;

class PulseManager
{
    public function __construct(
        protected TelemetryManager $telemetryManager,
    ) {
    }

    /**
     * @param  array<string, mixed>  $signal
     * @return array<string, mixed>
     */
    public function schedule(array $signal): array
    {
        $payload = [
            'status' => 'queued',
            'engine' => 'pulse',
            'signal_type' => $signal['type'] ?? 'unknown',
            'scheduled_at' => now()->toIso8601String(),
        ];

        $this->telemetryManager->record('pulse.schedule', $payload);

        return $payload;
    }
}
