<?php

namespace App\TitanCore\MCP\Handlers;

use App\TitanCore\Zylos\ZylosBridge;

class SkillStatusHandler
{
    public function __construct(protected ZylosBridge $bridge)
    {
    }

    /** @param array<string, mixed> $params */
    public function handle(array $params): array
    {
        return $this->bridge->executionStatus((string) ($params['execution_id'] ?? ''));
    }
}
