<?php

declare(strict_types=1);

namespace App\Contracts\Omni;

/**
 * InboundDriverContract — interface for drivers that handle inbound webhooks.
 */
interface InboundDriverContract extends OmniDriverContract
{
    /**
     * Verify the authenticity of an inbound webhook request.
     *
     * @param  array<string, string>  $headers
     */
    public function verify(array $headers, string $rawBody): bool;

    /**
     * Normalize a raw inbound webhook payload into a standard structure.
     *
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function normalize(array $headers, string $rawBody): array;
}
