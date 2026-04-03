<?php

namespace App\TitanCore\MCP\Handlers;

use App\TitanCore\Zero\Memory\TitanMemoryService;

class MemoryStoreHandler
{
    public function __construct(protected TitanMemoryService $memory)
    {
    }

    /** @param array<string, mixed> $params */
    public function handle(array $params): array
    {
        $key       = (string) ($params['key'] ?? '');
        $payload   = (array) ($params['payload'] ?? []);
        $companyId = isset($params['company_id']) ? (int) $params['company_id'] : null;

        $this->memory->store($key, $payload, $companyId);

        return ['ok' => true, 'key' => $key];
    }
}
