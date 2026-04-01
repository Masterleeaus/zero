<?php

namespace App\TitanCore\Zero\AI\Nexus;

class AuthorityWeights
{
    public function current(): array
    {
        return config('titan_core.nexus.authority_weights', [
            'logi' => 0.22,
            'creator' => 0.08,
            'finance' => 0.18,
            'micro' => 0.12,
            'macro' => 0.14,
            'entropy' => 0.10,
            'equilibrium' => 0.16,
        ]);
    }
}
