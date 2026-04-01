<?php

namespace App\TitanCore\Registry;

use App\TitanCore\Contracts\CoreModuleContract;

final class CoreModuleDefinition implements CoreModuleContract
{
    /**
     * @param  array<int, string>  $dependencies
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private readonly string $keyName,
        private readonly string $displayLabel,
        private readonly array $dependencies = [],
        private readonly int $priority = 100,
        private readonly array $metadata = [],
    ) {
    }

    public function key(): string
    {
        return $this->keyName;
    }

    public function label(): string
    {
        return $this->displayLabel;
    }

    public function dependencies(): array
    {
        return $this->dependencies;
    }

    public function bootPriority(): int
    {
        return $this->priority;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }
}
