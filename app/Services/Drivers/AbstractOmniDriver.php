<?php

declare(strict_types=1);

namespace App\Services\Drivers;

use App\Contracts\Omni\OmniDriverContract;
use Illuminate\Support\Facades\Log;

/**
 * AbstractOmniDriver — base class for all Omni transport drivers.
 *
 * Provides:
 *  - Config injection
 *  - isConfigured() via requiredConfigKeys()
 *  - Default ping() delegating to isConfigured()
 *  - Structured logging via log()
 */
abstract class AbstractOmniDriver implements OmniDriverContract
{
    /** @var array<string, mixed> */
    protected array $config;

    /** @param array<string, mixed> $config */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    abstract public function getChannelType(): string;

    /**
     * Returns true when every required config key is present and non-empty.
     */
    public function isConfigured(): bool
    {
        foreach ($this->requiredConfigKeys() as $key) {
            if (empty($this->config[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Override in concrete drivers to declare which config keys are mandatory.
     *
     * @return string[]
     */
    protected function requiredConfigKeys(): array
    {
        return [];
    }

    public function ping(): bool
    {
        return $this->isConfigured();
    }

    /**
     * Emit a structured log entry scoped to this driver and event.
     *
     * @param array<string, mixed> $context
     */
    protected function log(string $event, array $context = []): void
    {
        Log::info("omni.driver.{$this->getChannelType()}.{$event}", $context);
    }
}
