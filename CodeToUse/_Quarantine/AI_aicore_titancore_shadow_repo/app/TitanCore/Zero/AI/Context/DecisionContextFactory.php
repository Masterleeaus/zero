<?php

namespace App\TitanCore\Zero\AI\Context;

class DecisionContextFactory
{
    /**
     * @param  array<string, mixed>  $envelope
     * @param  array<string, mixed>  $knowledge
     * @param  array<string, mixed>  $memory
     * @return array<string, mixed>
     */
    public function make(array $envelope, array $knowledge = [], array $memory = []): array
    {
        return [
            'envelope' => $envelope,
            'knowledge' => $knowledge,
            'memory' => $memory,
            'surface' => $envelope['surface'] ?? 'business-suite',
            'company_id' => $envelope['company_id'] ?? null,
            'team_id' => $envelope['team_id'] ?? null,
            'user_id' => $envelope['user_id'] ?? null,
        ];
    }
}
