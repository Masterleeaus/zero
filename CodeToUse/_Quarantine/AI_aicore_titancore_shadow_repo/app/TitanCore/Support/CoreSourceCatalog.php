<?php

namespace App\TitanCore\Support;

class CoreSourceCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('titan_core.sources', []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function extractionOrder(): array
    {
        $sources = array_values($this->all());

        usort($sources, function (array $left, array $right): int {
            return (int) ($left['priority'] ?? 100) <=> (int) ($right['priority'] ?? 100);
        });

        return $sources;
    }
}
