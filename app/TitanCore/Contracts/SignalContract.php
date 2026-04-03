<?php

namespace App\TitanCore\Contracts;

interface SignalContract
{
    /**
     * Build and return a validated signal envelope.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function envelope(array $context = []): array;

    /**
     * Publish a signal through the dispatch pipeline.
     *
     * @param  array<string, mixed>  $processPayload
     * @param  array<string, mixed>  $signalPayload
     * @return array<string, mixed>
     */
    public function recordAndPublish(array $processPayload, array $signalPayload = []): array;
}
