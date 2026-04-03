<?php

namespace App\TitanCore\Agents;

use App\TitanCore\Zero\Telemetry\TelemetryManager;

class AgentStudioManager
{
    public function __construct(
        protected TelemetryManager $telemetryManager,
    ) {
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    public function draft(array $definition): array
    {
        $payload = [
            'status' => 'drafted',
            'type' => $definition['type'] ?? 'business-agent',
            'name' => $definition['name'] ?? 'Untitled Agent',
            'created_at' => now()->toIso8601String(),
        ];

        $this->telemetryManager->record('agents.draft.created', $payload);

        return $payload;
    }
}
