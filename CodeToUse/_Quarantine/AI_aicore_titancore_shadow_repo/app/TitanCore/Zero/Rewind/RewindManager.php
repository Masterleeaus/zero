<?php

namespace App\TitanCore\Zero\Rewind;

use App\TitanCore\Zero\Telemetry\TelemetryManager;

class RewindManager
{
    public function __construct(
        protected TelemetryManager $telemetryManager,
    ) {
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function begin(string $processId, array $context = []): array
    {
        $payload = [
            'process_id' => $processId,
            'state' => 'rewinding',
            'context' => $context,
            'started_at' => now()->toIso8601String(),
        ];

        $this->telemetryManager->record('zero.rewind.begin', $payload);

        return $payload;
    }
}
