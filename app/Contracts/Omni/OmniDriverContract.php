<?php

declare(strict_types=1);

namespace App\Contracts\Omni;

/**
 * OmniDriverContract — base interface for all Omni transport drivers.
 *
 * Every driver must report its channel type, confirm whether it is
 * fully configured, and support a lightweight connectivity check.
 */
interface OmniDriverContract
{
    /**
     * Returns the canonical channel identifier (e.g. 'sms', 'email', 'webchat').
     */
    public function getChannelType(): string;

    /**
     * Returns true when all required config keys are present and non-empty.
     */
    public function isConfigured(): bool;

    /**
     * Lightweight connectivity / readiness check — must be mockable in tests.
     */
    public function ping(): bool;
}
