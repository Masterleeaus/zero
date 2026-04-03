<?php

namespace App\TitanCore\Zero\Knowledge;

class KnowledgeManager
{
    public function __construct(
        protected KnowledgeScopeResolver $scopeResolver,
    ) {
    }

    /**
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    public function resolve(array $envelope): array
    {
        return [
            'status' => config('titan_core.knowledge.mode', 'deferred'),
            'scope' => $this->scopeResolver->scope($envelope),
            'sources' => ['zero-main', 'titan-ai-residual', 'ai-cores'],
        ];
    }
}
