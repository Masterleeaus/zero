<?php

declare(strict_types=1);

namespace App\Contracts\Omni;

/**
 * ProviderAuthContract — interface for drivers that manage provider authentication lifecycle.
 *
 * getCredentials() must NEVER return raw secrets — only masked / meta information.
 */
interface ProviderAuthContract extends OmniDriverContract
{
    /**
     * Perform the initial authentication handshake with the provider.
     */
    public function authenticate(): bool;

    /**
     * Refresh (rotate) credentials with the provider.
     */
    public function refreshCredentials(): void;

    /**
     * Return masked / meta credential information — never raw secrets.
     *
     * @return array<string, mixed>
     */
    public function getCredentials(): array;
}
