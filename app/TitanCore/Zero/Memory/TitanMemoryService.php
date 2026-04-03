<?php

namespace App\TitanCore\Zero\Memory;

use App\TitanCore\Contracts\MemoryContract;
use App\TitanCore\Zero\Memory\Session\SessionHandoffManager;
use Illuminate\Support\Facades\Cache;

/**
 * TitanMemoryService — unified memory access layer.
 *
 * Implements store / recall / snapshot / expire backed by Laravel Cache
 * with company_id tenancy enforcement and session-handoff support.
 */
class TitanMemoryService implements MemoryContract
{
    public function __construct(
        protected SessionHandoffManager $handoffManager,
        protected MemoryManager $memoryManager,
    ) {
    }

    /**
     * Build a cache key that is always scoped to company_id.
     */
    protected function cacheKey(string $key, ?int $companyId): string
    {
        if ($companyId === null) {
            if (app()->isProduction()) {
                \Illuminate\Support\Facades\Log::warning('TitanMemoryService: company_id is null — using global scope. This should not happen in production.', [
                    'key' => $key,
                ]);
            }
            return "titan.memory.global.{$key}";
        }

        return "titan.memory.{$companyId}.{$key}";
    }

    public function store(string $key, array $payload, ?int $companyId = null): void
    {
        $ttl = (int) config('titan_core.memory.ttl', 3600);

        Cache::put($this->cacheKey($key, $companyId), array_merge($payload, [
            'company_id' => $companyId,
            'stored_at'  => now()->toIso8601String(),
        ]), $ttl);
    }

    public function recall(string $key, ?int $companyId = null): ?array
    {
        return Cache::get($this->cacheKey($key, $companyId));
    }

    public function snapshot(string $key): array
    {
        return $this->memoryManager->snapshot($key);
    }

    public function expire(string $key, ?int $companyId = null): void
    {
        Cache::forget($this->cacheKey($key, $companyId));
    }
}
