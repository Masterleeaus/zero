<?php

namespace App\TitanCore\Zero\AI;

use App\TitanCore\Registry\Runtime\RuntimeCatalog;
use App\TitanCore\Zero\AI\Consensus\ConsensusCoordinator;
use App\TitanCore\Zero\AI\Context\DecisionContextFactory;
use App\TitanCore\Zero\AI\Context\InstructionBuilder;
use App\TitanCore\Zero\AI\Nexus\NexusCoordinator;
use App\TitanCore\Zero\AI\Runtime\RuntimeManager;
use App\TitanCore\Zero\Knowledge\KnowledgeManager;
use App\TitanCore\Zero\Memory\MemoryManager;
use App\TitanCore\Zero\Telemetry\TelemetryManager;

class ZeroCoreManager
{
    public function __construct(
        protected RuntimeManager $runtimeManager,
        protected RuntimeCatalog $runtimeCatalog,
        protected InstructionBuilder $instructionBuilder,
        protected DecisionContextFactory $contextFactory,
        protected ConsensusCoordinator $consensus,
        protected NexusCoordinator $nexus,
        protected KnowledgeManager $knowledgeManager,
        protected MemoryManager $memoryManager,
        protected TelemetryManager $telemetryManager,
    ) {
    }

    public function decide(array $envelope): array
    {
        $memory = $this->memoryManager->snapshot((string) ($envelope['id'] ?? 'global'));
        $knowledge = $this->knowledgeManager->resolve($envelope);
        $context = $this->contextFactory->make($envelope, $knowledge, $memory);
        $instruction = $this->instructionBuilder->build($envelope, $context);

        $result = $this->runtimeManager->adapter()->execute($instruction, $context);
        $decision = $this->consensus->resolve($result, ['envelope_id' => $envelope['id'] ?? null]);
        $nexus = $this->nexus->evaluate($envelope, $knowledge, $memory);

        $this->telemetryManager->record('zero.ai.decide', [
            'envelope_id' => $envelope['id'] ?? null,
            'runtime' => $result['runtime'] ?? 'unknown',
            'runtime_definition' => $this->runtimeCatalog->find((string) ($result['runtime'] ?? 'null')),
            'status' => $result['status'] ?? 'unknown',
            'consensus' => $decision,
            'nexus_winner' => $nexus['winner']['core'] ?? null,
        ]);

        return array_merge($result, [
            'decision' => $decision,
            'nexus' => $nexus,
        ]);
    }
}
