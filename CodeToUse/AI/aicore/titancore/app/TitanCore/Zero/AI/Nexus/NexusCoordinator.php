<?php

namespace App\TitanCore\Zero\AI\Nexus;

use App\TitanCore\Zero\AI\Nexus\Cores\CreatorCore;
use App\TitanCore\Zero\AI\Nexus\Cores\EntropyCore;
use App\TitanCore\Zero\AI\Nexus\Cores\EquilibriumCore;
use App\TitanCore\Zero\AI\Nexus\Cores\FinanceCore;
use App\TitanCore\Zero\AI\Nexus\Cores\LogiCore;
use App\TitanCore\Zero\AI\Nexus\Cores\MacroCore;
use App\TitanCore\Zero\AI\Nexus\Cores\MicroCore;

class NexusCoordinator
{
    public function __construct(
        protected UnifiedContextPackBuilder $contextPackBuilder,
        protected AuthorityWeights $weights,
        protected CritiqueLoopEngine $critiqueLoop,
        protected RoundRobinRefinement $refinement,
    ) {
    }

    public function evaluate(array $envelope, array $knowledge = [], array $memory = []): array
    {
        $contextPack = $this->contextPackBuilder->build($envelope, $knowledge, $memory);
        $weights = $this->weights->current();

        $cores = [
            new LogiCore(),
            new CreatorCore(),
            new FinanceCore(),
            new MicroCore(),
            new MacroCore(),
            new EntropyCore(),
            new EquilibriumCore(),
        ];

        $votes = [];
        foreach ($cores as $core) {
            $key = $core->key();
            $votes[] = $core->evaluate($contextPack, [
                'authority_weight' => $weights[$key] ?? 0,
                'default_confidence' => config('titan_core.ai.minimum_confidence', 0.7),
            ]);
        }

        $votes = $this->critiqueLoop->run($votes);
        $votes = $this->refinement->refine($votes);

        usort($votes, static function (array $a, array $b) {
            return ($b['authority_weight'] <=> $a['authority_weight']) ?: (($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0));
        });

        return [
            'context_pack' => $contextPack,
            'votes' => $votes,
            'winner' => $votes[0] ?? null,
            'weights' => $weights,
        ];
    }
}
