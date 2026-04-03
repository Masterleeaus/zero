<?php

namespace App\TitanCore\Zero\Telemetry;

class TelemetryManager
{
    /** @var array<int, array<string, mixed>> */
    protected array $events = [];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function record(string $event, array $payload = []): void
    {
        $this->events[] = [
            'event' => $event,
            'payload' => $payload,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->events;
    }
}
