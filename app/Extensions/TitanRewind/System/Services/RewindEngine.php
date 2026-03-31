<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Models\RewindConflict;
use Illuminate\Support\Facades\DB;

class RewindEngine
{
    public function __construct(
        private readonly RewindCaseService $cases,
        private readonly RewindAuditService $audit,
        private readonly RewindImpactAnalyzer $impactAnalyzer,
        private readonly RewindConflictDetector $conflicts,
        private readonly RewindRollbackProcessor $rollback,
        private readonly RewindNotificationService $notifications,
        private readonly RewindResolutionService $resolution,
        private readonly RewindSnapshotService $snapshots,
        private readonly RewindSignalIntegrationService $integration,
    ) {}

    public function initiate(array $payload): RewindCase
    {
        if (!$this->integration->canInitiateFromProcess((int) $payload['company_id'], $payload['process_id'] ?? null)) {
            throw new \RuntimeException('Process is not in a rewindable state or does not belong to the company scope.');
        }

        return DB::transaction(function () use ($payload) {
            $impact = $this->impactAnalyzer->analyze($payload);
            $case = $this->cases->openCase([
                'company_id' => $payload['company_id'],'team_id' => $payload['team_id'] ?? null,'user_id' => $payload['user_id'] ?? null,
                'title' => $payload['title'] ?? ('Rewind ' . ($payload['entity_type'] ?? 'process')),'status' => 'rewinding','severity' => $payload['severity'] ?? 'high',
                'source_type' => $payload['source_type'] ?? 'manual','source_id' => $payload['source_id'] ?? null,'process_id' => $payload['process_id'] ?? null,
                'entity_type' => $payload['entity_type'] ?? null,'entity_id' => $payload['entity_id'] ?? null,
                'meta_json' => ['reason' => $payload['reason'] ?? null,'impact' => $impact, 'integration' => ['initiated_from_signal' => ($payload['source_type'] ?? null) === 'signal']],
            ]);

            foreach ($impact['downstream'] as $item) {
                DB::table('tz_rewind_links')->insert([
                    'company_id' => $payload['company_id'],'team_id' => $payload['team_id'] ?? null,'user_id' => $payload['user_id'] ?? null,'case_id' => $case->id,
                    'parent_process_id' => $payload['process_id'] ?? null,'child_process_id' => $item['child_process_id'],'parent_entity_type' => $payload['entity_type'] ?? null,
                    'parent_entity_id' => $payload['entity_id'] ?? null,'child_entity_type' => $item['child_entity_type'],'child_entity_id' => $item['child_entity_id'],
                    'relationship_type' => $item['relationship_type'] ?? 'cascade','depth' => $item['depth'] ?? 1,
                    'can_reuse' => $item['can_reuse'],'must_reissue' => $item['must_reissue'],'status' => 'held','action_required' => $item['action_required'] ?? null,
                    'held_reason' => 'Parent process is being rewound.','meta_json' => json_encode($item),'created_at' => now(),'updated_at' => now(),
                ]);
            }

            $snapshotCount = $this->snapshots->captureInitiationSnapshots($case, $impact);

            if (($payload['entity_type'] ?? null) === 'payments') {
                RewindConflict::query()->create([
                    'company_id' => $payload['company_id'],'team_id' => $payload['team_id'] ?? null,'user_id' => $payload['user_id'] ?? null,'case_id' => $case->id,
                    'process_id' => $payload['process_id'] ?? null,'entity_type' => 'payments','entity_id' => $payload['entity_id'] ?? null,
                    'conflict_type' => 'external-transaction','severity' => 'critical','status' => 'open',
                    'message' => 'Payment rewind requires refund/reissue review.','details_json' => ['resolution' => 'manual review and refund workflow'],
                    'resolution_hint' => 'Initiate refund, then create corrected payment process.',
                ]);
            }

            $detected = $this->conflicts->detectForCase($case);
            $this->conflicts->persistForCase($case, $detected);
            if (collect($detected)->contains(fn ($conflict) => ($conflict['severity'] ?? null) === 'critical')) {
                $case->status = 'conflict-hold';
                $case->save();
            }

            $this->integration->promoteCaseLifecycle($case, config('titan-rewind.process_bridge.rewind_state', 'rewinding'), [
                'type' => $payload['actor_type'] ?? 'user',
                'id' => $payload['actor_id'] ?? $payload['user_id'] ?? null,
            ], ['reason' => $payload['reason'] ?? null]);
            $this->integration->emitPulseHooks($case, 'initiated', ['impact' => $impact, 'snapshot_count' => $snapshotCount]);

            $this->audit->appendEvent([
                'company_id' => $payload['company_id'],'team_id' => $payload['team_id'] ?? null,'user_id' => $payload['user_id'] ?? null,'case_id' => $case->id,
                'event_type' => 'rewind_initiated','entity_type' => $payload['entity_type'] ?? null,'entity_id' => $payload['entity_id'] ?? null,
                'actor_type' => $payload['actor_type'] ?? 'user','actor_id' => $payload['actor_id'] ?? $payload['user_id'] ?? null,
                'payload_json' => ['reason' => $payload['reason'] ?? null,'impact' => $impact, 'conflicts' => $detected, 'snapshot_count' => $snapshotCount],'idempotency_key' => 'rewind_initiated:' . ($payload['process_id'] ?? $case->id),
            ]);

            $this->notifications->queueCaseNotification($case, 'rewind-initiated', 'A rewind has been initiated and downstream work has been placed on hold.', ['impact' => $impact]);

            return $case;
        });
    }

    public function submitCorrection(RewindCase $case, array $correctionData, array $actor): array
    {
        $detected = $this->conflicts->detectForCase($case, $correctionData);
        $this->conflicts->persistForCase($case, $detected);
        if (collect($detected)->contains(fn ($conflict) => ($conflict['severity'] ?? null) === 'critical')) {
            $case->status = 'conflict-hold';
            $case->save();
            $this->integration->emitPulseHooks($case, 'conflict', ['conflicts' => $detected]);
        }

        return $this->rollback->submitCorrection($case, $correctionData, $actor);
    }

    public function completeRollback(RewindCase $case, array $actor, array $options = []): array
    {
        return $this->rollback->completeRollback($case, $actor, $options);
    }

    public function resolveConflict(RewindCase $case, RewindConflict $conflict, array $actor, string $resolution, array $notes = []): RewindConflict
    {
        return $this->resolution->resolveConflict($case, $conflict, $actor, $resolution, $notes);
    }
}
