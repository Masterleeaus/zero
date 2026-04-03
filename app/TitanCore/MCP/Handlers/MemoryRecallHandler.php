<?php

namespace App\TitanCore\MCP\Handlers;

use App\TitanCore\Zero\Memory\TitanMemoryService;

class MemoryRecallHandler
{
    public function __construct(protected TitanMemoryService $memory)
    {
    }

    /** @param array<string, mixed> $params */
    public function handle(array $params): array
    {
        $key       = (string) ($params['key'] ?? '');
        $companyId = isset($params['company_id']) ? (int) $params['company_id'] : null;

        $result = $this->memory->recall($key, $companyId);

        return ['ok' => $result !== null, 'key' => $key, 'data' => $result];
    }
}
