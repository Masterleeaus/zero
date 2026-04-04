<?php

namespace App\TitanCore\Zero\AI\Consensus;

class ConsensusCoordinator
{
    /**
     * @param  array<string, mixed>  $decision
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function resolve(array $decision, array $context = []): array
    {
        $confidence = (float) ($decision['confidence'] ?? 0.5);
        $requiresApproval = $confidence < (float) config('titan_core.ai.minimum_confidence', 0.7);

        return [
            'approved' => ! $requiresApproval,
            'requires_approval' => $requiresApproval,
            'confidence' => $confidence,
            'strategy' => $requiresApproval ? 'human_gate' : 'zero_authority',
            'context' => $context,
        ];
    }
}
