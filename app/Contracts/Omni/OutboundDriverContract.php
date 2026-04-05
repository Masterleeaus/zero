<?php

declare(strict_types=1);

namespace App\Contracts\Omni;

/**
 * OutboundDriverContract — interface for drivers that can send messages.
 */
interface OutboundDriverContract extends OmniDriverContract
{
    /**
     * Send a single message payload via the channel provider.
     *
     * @param  array<string, mixed>  $payload
     * @return array{status: string, provider_message_id: string|null, raw: mixed}
     */
    public function send(array $payload): array;

    /**
     * Send a batch of message payloads via the channel provider.
     *
     * @param  array<int, array<string, mixed>>  $payloads
     * @return array<int, array{status: string, provider_message_id: string|null, raw: mixed}>
     */
    public function sendBatch(array $payloads): array;
}
