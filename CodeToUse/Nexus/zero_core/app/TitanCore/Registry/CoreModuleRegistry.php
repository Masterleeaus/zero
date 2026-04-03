<?php

namespace App\TitanCore\Registry;

use App\TitanCore\Contracts\CoreModuleContract;
use RuntimeException;

class CoreModuleRegistry
{
    /** @var array<string, CoreModuleContract> */
    protected array $modules = [];

    public function register(CoreModuleContract $module): void
    {
        $this->modules[$module->key()] = $module;
    }

    /**
     * @return array<string, CoreModuleContract>
     */
    public function all(): array
    {
        uasort($this->modules, fn (CoreModuleContract $a, CoreModuleContract $b) => $a->bootPriority() <=> $b->bootPriority());

        return $this->modules;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->modules);
    }

    public function get(string $key): CoreModuleContract
    {
        if (! $this->has($key)) {
            throw new RuntimeException("Unknown Titan core module [{$key}].");
        }

        return $this->modules[$key];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function dependencyMap(): array
    {
        $map = [];

        foreach ($this->all() as $key => $module) {
            $map[$key] = $module->dependencies();
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    public function enabledKeys(): array
    {
        return array_keys($this->all());
    }
}
