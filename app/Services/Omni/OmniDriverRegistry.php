<?php

declare(strict_types=1);

namespace App\Services\Omni;

use App\Contracts\Omni\InboundDriverContract;
use App\Contracts\Omni\OmniDriverContract;
use App\Contracts\Omni\OutboundDriverContract;
use RuntimeException;

/**
 * OmniDriverRegistry — runtime registry for all Omni transport drivers.
 *
 * Drivers are keyed by their channel type string (e.g. 'sms', 'email').
 * The registry is registered as a singleton in TitanCoreServiceProvider
 * and pre-populated with all configured channel drivers.
 */
class OmniDriverRegistry
{
    /** @var array<string, OmniDriverContract> */
    private array $drivers = [];

    /**
     * Register a driver. The driver's getChannelType() is used as the key.
     */
    public function register(OmniDriverContract $driver): void
    {
        $this->drivers[$driver->getChannelType()] = $driver;
    }

    /**
     * Retrieve a driver by channel type.
     *
     * @throws RuntimeException if no driver is registered for the given channel type.
     */
    public function get(string $channelType): OmniDriverContract
    {
        if (!$this->has($channelType)) {
            throw new RuntimeException("No Omni driver registered for channel type: {$channelType}");
        }

        return $this->drivers[$channelType];
    }

    /**
     * Returns true if a driver is registered for the given channel type.
     */
    public function has(string $channelType): bool
    {
        return isset($this->drivers[$channelType]);
    }

    /**
     * Returns all registered drivers keyed by channel type.
     *
     * @return array<string, OmniDriverContract>
     */
    public function all(): array
    {
        return $this->drivers;
    }

    /**
     * Returns all drivers that implement OutboundDriverContract.
     *
     * @return array<string, OutboundDriverContract>
     */
    public function allOutbound(): array
    {
        return array_filter(
            $this->drivers,
            static fn (OmniDriverContract $d) => $d instanceof OutboundDriverContract
        );
    }

    /**
     * Returns all drivers that implement InboundDriverContract.
     *
     * @return array<string, InboundDriverContract>
     */
    public function allInbound(): array
    {
        return array_filter(
            $this->drivers,
            static fn (OmniDriverContract $d) => $d instanceof InboundDriverContract
        );
    }

    /**
     * Returns all drivers where isConfigured() returns true.
     *
     * @return array<string, OmniDriverContract>
     */
    public function configured(): array
    {
        return array_filter(
            $this->drivers,
            static fn (OmniDriverContract $d) => $d->isConfigured()
        );
    }
}
