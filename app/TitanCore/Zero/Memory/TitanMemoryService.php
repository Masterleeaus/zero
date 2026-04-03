<?php

namespace App\TitanCore\Zero\Memory;

use App\TitanCore\Contracts\MemoryContract;

/**
 * @deprecated Use App\Titan\Core\TitanMemoryService (canonical).
 *
 * This file is retained as a tombstone to prevent namespace drift.
 * The canonical DB-backed, rewind-compatible memory runtime is:
 *   App\Titan\Core\TitanMemoryService
 *
 * This lightweight cache-backed accessor is preserved only because MemoryManager
 * needs a snapshot() helper. It must NOT be injected into MCP handlers, the AI
 * router, or the admin panel.
 */
class TitanMemoryService implements MemoryContract
{
    public function __construct(
        protected SessionHandoffManager $handoffManager,
        protected MemoryManager $memoryManager,
    ) {
    }

    /**
     * Build a cache key scoped to company_id.
     */
    protected function cacheKey(string $key, ?int $companyId): string
    {
        if ($companyId === null) {
            if (app()->isProduction()) {
                \Illuminate\Support\Facades\Log::warning('TitanMemoryService (cache): company_id is null — global scope used.', ['key' => $key]);
            }
            return "titan.memory.global.{$key}";
        }

        return "titan.memory.{$companyId}.{$key}";
    }

    public function store(string $key, array $payload, ?int $companyId = null): void
    {
        $ttl = (int) config('titan_core.memory.ttl', 3600);
        \Illuminate\Support\Facades\Cache::put($this->cacheKey($key, $companyId), array_merge($payload, [
            'company_id' => $companyId,
            'stored_at'  => now()->toIso8601String(),
        ]), $ttl);
    }

    public function recall(string $key, ?int $companyId = null): ?array
    {
        return \Illuminate\Support\Facades\Cache::get($this->cacheKey($key, $companyId));
    }

    public function snapshot(string $key): array
    {
        return $this->memoryManager->snapshot($key);
    }

    public function expire(string $key, ?int $companyId = null): void
    {
        \Illuminate\Support\Facades\Cache::forget($this->cacheKey($key, $companyId));
    }
}
