<?php

namespace App\TitanCore\Zero\AI\Runtime;

use InvalidArgumentException;

class RuntimeManager
{
    /** @var array<string, RuntimeAdapterContract> */
    protected array $adapters = [];

    public function __construct()
    {
        $this->register(new NullRuntimeAdapter());
    }

    public function register(RuntimeAdapterContract $adapter): void
    {
        $this->adapters[$adapter->key()] = $adapter;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->adapters);
    }

    public function adapter(?string $key = null): RuntimeAdapterContract
    {
        $key ??= config('titan_core.ai.default_runtime', 'null');

        if (! array_key_exists($key, $this->adapters)) {
            throw new InvalidArgumentException("Unknown runtime adapter [{$key}].");
        }

        return $this->adapters[$key];
    }
}
