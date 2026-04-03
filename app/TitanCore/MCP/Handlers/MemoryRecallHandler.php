<?php

namespace App\TitanCore\MCP\Handlers;

use App\Titan\Core\TitanMemoryService;

class MemoryRecallHandler
{
    public function __construct(protected TitanMemoryService $memory)
    {
    }

    /**
     * Handle a titan.memory.recall MCP request.
     *
     * Expected params:
     *   - company_id  (int, required)
     *   - session_id  (string, required) — may also be passed as 'key' for backwards compat
     *   - query       (string, optional) — semantic search query
     *   - type        (string, optional) — filter by memory type
     *   - limit       (int, optional, default 20)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function handle(array $params): array
    {
        $companyId = (int) ($params['company_id'] ?? 0);
        $sessionId = (string) ($params['session_id'] ?? $params['key'] ?? '');

        if ($companyId === 0 || $sessionId === '') {
            return ['ok' => false, 'error' => 'company_id and session_id are required'];
        }

        $options = array_filter([
            'query'          => $params['query'] ?? null,
            'type'           => $params['type'] ?? null,
            'limit'          => isset($params['limit']) ? (int) $params['limit'] : 20,
            'semantic_limit' => isset($params['semantic_limit']) ? (int) $params['semantic_limit'] : 5,
        ], fn ($v) => $v !== null);

        $result = $this->memory->recall($companyId, $sessionId, $options);

        return ['ok' => true, 'session_id' => $sessionId, 'data' => $result];
    }
}
