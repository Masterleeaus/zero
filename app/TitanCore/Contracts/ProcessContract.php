<?php

namespace App\TitanCore\Contracts;

interface ProcessContract
{
    /**
     * Record a new process transition.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function record(array $payload): array;

    /**
     * Transition a process to a new state.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function transition(string $processId, string $state, array $metadata = []): array;

    /**
     * Return the allowed state-transition map.
     *
     * @return array<string, array<int, string>>
     */
    public function map(): array;
}
