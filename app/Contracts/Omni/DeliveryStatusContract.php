<?php

declare(strict_types=1);

namespace App\Contracts\Omni;

/**
 * DeliveryStatusContract — interface for drivers that can parse delivery status callbacks.
 */
interface DeliveryStatusContract extends OmniDriverContract
{
    /**
     * Parse a delivery-status webhook payload into a normalised structure.
     *
     * @param  array<string, mixed>  $payload
     * @return array{provider_message_id: string|null, status: string, timestamp: string|null, raw: mixed}
     */
    public function parseStatus(array $payload): array;
}
