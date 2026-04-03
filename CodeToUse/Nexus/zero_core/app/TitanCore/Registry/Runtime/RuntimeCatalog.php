<?php

namespace App\TitanCore\Registry\Runtime;

class RuntimeCatalog
{
    /**
     * @var array<string, RuntimeDefinition>
     */
    protected array $definitions = [];

    public function register(RuntimeDefinition $definition): void
    {
        $this->definitions[$definition->key] = $definition;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function manifest(): array
    {
        return array_values(array_map(static fn (RuntimeDefinition $definition): array => $definition->toArray(), $this->definitions));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $key): ?array
    {
        return isset($this->definitions[$key]) ? $this->definitions[$key]->toArray() : null;
    }
}
