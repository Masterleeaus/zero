<?php

namespace App\TitanCore\Registry\Tools;

class ToolRegistry
{
    /**
     * @var array<string, ToolDefinition>
     */
    protected array $definitions = [];

    public function register(ToolDefinition $definition): void
    {
        $this->definitions[$definition->key] = $definition;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function manifest(): array
    {
        return array_values(array_map(static fn (ToolDefinition $definition): array => $definition->toArray(), $this->definitions));
    }
}
