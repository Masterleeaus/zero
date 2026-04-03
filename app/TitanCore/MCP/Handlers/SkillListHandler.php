<?php

namespace App\TitanCore\MCP\Handlers;

use App\TitanCore\Zylos\ZylosBridge;

class SkillListHandler
{
    public function __construct(protected ZylosBridge $bridge)
    {
    }

    /** @param array<string, mixed> $params */
    public function handle(array $params): array
    {
        return $this->bridge->list();
    }
}
