<?php

namespace App\TitanCore\Zero\AI\Nexus;

abstract class AbstractNexusCore implements NexusCoreContract
{
    /**
     * @param  array<string, mixed>  $contextPack
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function evaluate(array $contextPack, array $options = []): array
    {
        return [
            'core' => $this->key(),
            'summary' => $this->summary($contextPack, $options),
            'confidence' => $this->confidence($contextPack, $options),
            'authority_weight' => $options['authority_weight'] ?? 0,
            'criticisms' => [],
            'recommendations' => $this->recommendations($contextPack, $options),
        ];
    }

    abstract protected function summary(array $contextPack, array $options = []): string;

    protected function recommendations(array $contextPack, array $options = []): array
    {
        return [];
    }

    protected function confidence(array $contextPack, array $options = []): float
    {
        return (float) ($options['default_confidence'] ?? 0.7);
    }
}
