<?php

namespace App\TitanCore\MCP\Handlers;

use App\TitanCore\Zylos\ZylosBridge;

class SkillDispatchHandler
{
    public function __construct(protected ZylosBridge $bridge)
    {
    }

    /** @param array<string, mixed> $params */
    public function handle(array $params): array
    {
        $skill   = (string) ($params['skill'] ?? '');
        $payload = (array) ($params['payload'] ?? $params);

        return $this->bridge->dispatch($skill, $payload);
    }
}
