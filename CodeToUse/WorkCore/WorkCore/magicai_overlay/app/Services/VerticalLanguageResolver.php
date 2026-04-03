<?php

namespace App\Services;

class VerticalLanguageResolver
{
    public function __construct(
        protected string $vertical,
        protected array $config = []
    ) {
    }

    public function vertical(): string
    {
        return $this->vertical;
    }

    public function label(string $key, ?string $fallback = null): string
    {
        $labels = data_get($this->config, $this->vertical . '.labels', []);

        return $labels[$key] ?? $fallback ?? str_replace(['-', '_'], ' ', $key);
    }

    public function menu(): array
    {
        return data_get($this->config, $this->vertical . '.menu', []);
    }
}
