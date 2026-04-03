<?php

namespace App\TitanCore\Zero\AI;

use App\TitanCore\Events\TitanCoreActivity;
use App\TitanCore\Zero\AI\Context\DecisionContextFactory;
use App\TitanCore\Zero\AI\Context\InstructionBuilder;
use App\TitanCore\Zero\Budget\TitanTokenBudget;
use App\TitanCore\Zero\Signals\SignalBridge;

class TitanAIRouter
{
    public function __construct(
        protected ZeroCoreManager $manager,
        protected DecisionContextFactory $contextFactory,
        protected InstructionBuilder $instructionBuilder,
        protected SignalBridge $signalBridge,
        protected TitanTokenBudget $budget,
    ) {
    }

    /**
     * Canonical public entry-point (alias for execute).
     *
     * All callers — MCP, Omni, controllers — MUST route through here.
     *
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    public function route(array $envelope): array
    {
        return $this->execute($envelope);
    }

    /**
     * Execute an AI request through the canonical TitanCore routing pipeline.
     *
     * Pipeline: Titan Omni → TitanAIRouter → TitanMemory → Signals → Pulse → Approval → Execution → Rewind
     *
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    public function execute(array $envelope): array
    {
        $envelope  = $this->normaliseEnvelope($envelope);
        $startTime = microtime(true);

        if (! $this->budget->isAllowed($envelope)) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            event(TitanCoreActivity::fromArray([
                'intent'     => $envelope['intent'] ?? 'ai.complete',
                'provider'   => 'titan_ai_router',
                'duration'   => $duration,
                'tokens'     => (int) ($envelope['tokens'] ?? 0),
                'company_id' => $envelope['company_id'] ?? null,
                'user_id'    => $envelope['user_id'] ?? null,
                'status'     => 'blocked',
            ]));

            return [
                'ok'     => false,
                'status' => 'budget_exceeded',
                'budget' => $this->budget->status($envelope),
            ];
        }

        $result   = $this->manager->decide($envelope);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $tokens   = (int) ($result['tokens_used'] ?? $result['tokens'] ?? 0);

        if ($tokens > 0) {
            $this->budget->record($envelope, $tokens);
        }

        $this->signalBridge->recordAndPublish(
            [
                'company_id'       => $envelope['company_id'] ?? null,
                'user_id'          => $envelope['user_id'] ?? null,
                'entity_type'      => 'ai_router',
                'domain'           => 'titan_core',
                'current_state'    => ($result['decision']['requires_approval'] ?? false)
                    ? 'awaiting-approval'
                    : 'processing',
                'originating_node' => 'titan_ai_router',
            ],
            [
                'company_id' => $envelope['company_id'] ?? null,
                'signals'    => [],
                'id'         => $envelope['id'] ?? null,
            ],
        );

        event(TitanCoreActivity::fromArray([
            'intent'     => $envelope['intent'] ?? 'ai.complete',
            'provider'   => $result['runtime'] ?? 'unknown',
            'duration'   => $duration,
            'tokens'     => $tokens,
            'company_id' => $envelope['company_id'] ?? null,
            'user_id'    => $envelope['user_id'] ?? null,
            'status'     => $result['status'] ?? 'ok',
        ]));

        return $result;
    }

    /**
     * Return router health/capability status.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        return [
            'router'                     => 'TitanAIRouter',
            'runtime'                    => config('titan_core.ai.default_runtime', 'null'),
            'model_router'               => config('titan_core.ai.model_router', 'zero'),
            'provider_selection'         => true,
            'nexus_execution'            => true,
            'authority_weighting'        => true,
            'critique_loop'              => true,
            'approval_state_aware'       => true,
            'signal_envelope_compatible' => true,
            'memory_injection'           => true,
            'mcp_layer'                  => 'active',
            'budget_enforcement'         => true,
            'activity_telemetry'         => true,
        ];
    }

    /**
     * Ensure company_id is always present and team_id is never used as tenant boundary.
     *
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    protected function normaliseEnvelope(array $envelope): array
    {
        if (empty($envelope['company_id']) && ! empty($envelope['team_id'])) {
            $envelope['company_id'] = $envelope['team_id'];
        }

        $envelope['stage'] = $envelope['stage'] ?? 'suggestion';

        return $envelope;
    }
}
