<?php

namespace App\TitanCore\Contracts;

interface MemoryContract
{
    /**
     * Store a memory entry under the given key, scoped to company_id.
     *
     * @param  array<string, mixed>  $payload
     */
    public function store(string $key, array $payload, ?int $companyId = null): void;

    /**
     * Recall a memory entry by key, scoped to company_id.
     *
     * @return array<string, mixed>|null
     */
    public function recall(string $key, ?int $companyId = null): ?array;

    /**
     * Snapshot the current memory state for a given session/process key.
     *
     * @return array<string, mixed>
     */
    public function snapshot(string $key): array;

    /**
     * Expire (delete) a memory entry by key.
     */
    public function expire(string $key, ?int $companyId = null): void;
}
