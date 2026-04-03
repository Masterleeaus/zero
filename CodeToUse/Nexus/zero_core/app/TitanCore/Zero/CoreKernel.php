<?php

namespace App\TitanCore\Zero;

use App\TitanCore\Agents\AgentStudioManager;
use App\TitanCore\Omni\OmniManager;
use App\TitanCore\Pulse\PulseManager;
use App\TitanCore\Registry\CoreManifest;
use App\TitanCore\Registry\CoreModuleRegistry;
use App\TitanCore\Support\CoreSourceCatalog;
use App\TitanCore\Zero\AI\ZeroCoreManager;
use App\TitanCore\Zero\Signals\SignalBridge;

class CoreKernel
{
    public function __construct(
        protected CoreModuleRegistry $modules,
        protected CoreManifest $manifest,
        protected CoreSourceCatalog $sources,
        protected ZeroCoreManager $zero,
        protected SignalBridge $signals,
        protected PulseManager $pulse,
        protected OmniManager $omni,
        protected AgentStudioManager $agents,
    ) {
    }

    public function status(): array
    {
        $sampleDecision = $this->zero->decide([
            'id' => 'kernel-status-sample',
            'signal_key' => 'titan.core.status',
            'stage' => 'status',
            'payload' => ['source' => 'status-page'],
        ]);

        return [
            'modules' => $this->modules->enabledKeys(),
            'dependencies' => $this->modules->dependencyMap(),
            'sources' => $this->sources->extractionOrder(),
            'manifest' => $this->manifest->toArray(),
            'ai_runtime' => config('titan_core.ai.default_runtime', 'null'),
            'nexus' => [
                'enabled_cores' => config('titan_core.nexus.enabled_cores', []),
                'authority_weights' => config('titan_core.nexus.authority_weights', []),
                'winner' => $sampleDecision['nexus']['winner'] ?? null,
                'votes' => $sampleDecision['nexus']['votes'] ?? [],
            ],
            'surfaces' => [
                'business_suite' => true,
                'pulse' => true,
                'omni' => true,
                'agents' => true,
            ],
        ];
    }
}
