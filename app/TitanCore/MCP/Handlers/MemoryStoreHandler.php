<?php

namespace App\TitanCore\MCP\Handlers;

use App\Titan\Core\TitanMemoryService;

class MemoryStoreHandler
{
    public function __construct(protected TitanMemoryService $memory)
    {
    }

    /**
     * Handle a titan.memory.store MCP request.
     *
     * Expected params:
     *   - company_id       (int, required)
     *   - user_id          (int, optional)
     *   - session_id       (string, required) — may also be passed as 'key' for backwards compat
     *   - type             (string, optional, default 'general')
     *   - content          (string, required) — may also be passed as serialised 'payload'
     *   - importance_score (float, optional)
     *   - expires_at       (string|null, optional)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function handle(array $params): array
    {
        $companyId = (int) ($params['company_id'] ?? 0);
        $userId    = (int) ($params['user_id'] ?? 0);
        $sessionId = (string) ($params['session_id'] ?? $params['key'] ?? '');

        // Accept content directly or fall back to JSON-encoded payload for backwards compat
        $rawPayload = $params['payload'] ?? null;
        $content    = (string) ($params['content'] ?? (is_string($rawPayload) ? $rawPayload : json_encode($rawPayload ?? [], JSON_UNESCAPED_UNICODE)));

        if ($companyId === 0 || $sessionId === '' || $content === '') {
            return ['ok' => false, 'error' => 'company_id, session_id, and content are required'];
        }

        $result = $this->memory->store(
            $companyId,
            $userId,
            $sessionId,
            (string) ($params['type'] ?? 'general'),
            $content,
            array_filter([
                'importance_score' => isset($params['importance_score']) ? (float) $params['importance_score'] : null,
                'expires_at'       => $params['expires_at'] ?? null,
            ], fn ($v) => $v !== null)
        );

        return ['ok' => true, 'session_id' => $sessionId, 'data' => $result];
    }
}
