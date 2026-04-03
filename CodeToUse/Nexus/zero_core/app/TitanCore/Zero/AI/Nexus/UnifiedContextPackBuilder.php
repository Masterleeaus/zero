<?php

namespace App\TitanCore\Zero\AI\Nexus;

class UnifiedContextPackBuilder
{
    public function build(array $envelope, array $knowledge = [], array $memory = []): array
    {
        return [
            'envelope' => $envelope,
            'knowledge' => $knowledge,
            'memory' => $memory,
            'ai' => [
                'minimum_confidence' => config('titan_core.ai.minimum_confidence', 0.7),
                'default_runtime' => config('titan_core.ai.default_runtime', 'null'),
            ],
            'nexus' => [
                'enabled_cores' => config('titan_core.nexus.enabled_cores', []),
                'authority_weights' => config('titan_core.nexus.authority_weights', []),
            ],
        ];
    }
}
