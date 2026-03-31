<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RewindRollbackProcessor
{
    public function __construct(
        private readonly RewindAuditService $audit,
        private readonly RewindNotificationService $notifications,
        private readonly RewindRollbackPlannerService $planner,
        private readonly RewindExternalTransactionService $externalTransactions,
        private readonly RewindSnapshotService $snapshots,
        private readonly RewindSignalIntegrationService $integration,
    ) {
    }

    public function submitCorrection(RewindCase $case, array $correctionData, array $actor): array
    {
        return DB::transaction(function () use ($case, $correctionData, $actor) {
            $correctionProcessId = (string) ($correctionData['process_id'] ?? ('corr-' . $case->id . '-' . now()->timestamp));
            $existingMeta = $case->meta_json ?? [];
            $existingMeta['correction'] = [
                'submitted_at' => now()->toIso8601String(),
                'submitted_by' => $actor,
                'correction_process_id' => $correctionProcessId,
                'correction_data' => $correctionData,
            ];

            $case->status = 'awaiting-correction';
            $case->correction_process_id = $correctionProcessId;
            $case->meta_json = $existingMeta;
            $case->save();

            DB::table('tz_rewind_links')
                ->where('company_id', $case->company_id)
                ->where('case_id', $case->id)
                ->update([
                    'status' => 'held',
                    'held_reason' => 'Parent rewind awaiting corrected process approval.',
                    'updated_at' => now(),
                ]);

            $this->integration->promoteCaseLifecycle($case, config('titan-rewind.process_bridge.correction_state', 'awaiting-correction'), $actor, [
                'correction_process_id' => $correctionProcessId,
            ]);
            $this->integration->emitPulseHooks($case, 'correction-submitted', ['correction_process_id' => $correctionProcessId]);

            $this->audit->appendEvent([
                'company_id' => $case->company_id,
                'team_id' => $case->team_id,
                'user_id' => $case->user_id,
                'case_id' => $case->id,
                'event_type' => 'correction_submitted',
                'entity_type' => $case->entity_type,
                'entity_id' => $case->entity_id,
                'actor_type' => $actor['type'] ?? 'user',
                'actor_id' => $actor['id'] ?? null,
                'payload_json' => ['correction_process_id' => $correctionProcessId, 'correction_data' => $correctionData],
                'idempotency_key' => 'correction_submitted:' . $case->id . ':' . $correctionProcessId,
            ]);

            $externalPlan = $this->externalTransactions->buildPlan($case, $this->planner->plan($case->fresh()));
            $queuedReversals = !empty($externalPlan) ? $this->externalTransactions->queueReversalActions($case, $actor) : 0;

            $notice = $this->notifications->queueCaseNotification(
                $case,
                'rewind-correction-submitted',
                'A correction was submitted and downstream work remains held until rollback completes.',
                ['correction_process_id' => $correctionProcessId]
            );

            return [
                'status' => 'correction-submitted',
                'case_id' => $case->id,
                'correction_process_id' => $correctionProcessId,
                'notifications' => $notice,
                'external_transactions' => $externalPlan,
                'queued_reversals' => $queuedReversals,
            ];
        });
    }

    public function completeRollback(RewindCase $case, array $actor, array $options = []): array
    {
        return DB::transaction(function () use ($case, $actor, $options) {
            $meta = $case->meta_json ?? [];
            $correction = Arr::get($meta, 'correction', []);
            $correctionProcessId = (string) ($options['correction_process_id'] ?? ($correction['correction_process_id'] ?? ('corr-' . $case->id)));
            $correctionEntityId = $options['correction_entity_id'] ?? Arr::get($correction, 'correction_data.entity_id');

            $meta['rollback'] = [
                'rolled_back_at' => now()->toIso8601String(),
                'rolled_back_by' => $actor,
                'correction_process_id' => $correctionProcessId,
                'correction_entity_id' => $correctionEntityId,
            ];
            $case->status = 'rolled-back';
            $case->replacement_process_id = $correctionProcessId;
            $case->meta_json = $meta;
            $case->rollback_completed_at = now();
            $case->resolved_at = now();
            $case->resolved_by_type = $actor['type'] ?? 'user';
            $case->resolved_by_id = $actor['id'] ?? null;
            $case->save();

            $reused = [];
            $reissued = [];
            $links = DB::table('tz_rewind_links')->where('company_id', $case->company_id)->where('case_id', $case->id)->get();
            foreach ($links as $link) {
                $mustReissue = (bool) $link->must_reissue;
                $status = $mustReissue ? 'ready-for-reissue' : 'reused';
                $actionType = $mustReissue ? 'marked-for-reissue' : 'updated-for-correction';
                $actionPayload = [
                    'case_id' => $case->id,
                    'correction_process_id' => $correctionProcessId,
                    'relationship_type' => $link->relationship_type,
                    'child_process_id' => $link->child_process_id,
                ];

                DB::table('tz_rewind_links')
                    ->where('id', $link->id)
                    ->update([
                        'status' => $status,
                        'action_required' => $mustReissue ? 'reissue' : 'resume',
                        'held_reason' => $mustReissue ? 'Must be reissued from corrected parent.' : 'Can resume from corrected parent.',
                        'updated_at' => now(),
                    ]);

                DB::table('titan_rewind_actions')->insert([
                    'company_id' => $case->company_id,
                    'team_id' => $case->team_id,
                    'user_id' => $case->user_id,
                    'case_id' => $case->id,
                    'fix_id' => null,
                    'action_type' => $actionType,
                    'target_type' => $link->child_entity_type,
                    'target_id' => $link->child_entity_id,
                    'before_json' => json_encode(['link_id' => $link->id, 'status' => $link->status ?? 'held']),
                    'after_json' => json_encode($actionPayload),
                    'executed_by_type' => $actor['type'] ?? 'user',
                    'executed_by_id' => $actor['id'] ?? null,
                    'executed_at' => now(),
                    'success' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($mustReissue) {
                    $reissued[] = $link->child_process_id;
                } else {
                    $reused[] = $link->child_process_id;
                }
            }

            $plan = $this->planner->plan($case->fresh());
            $snapshotCount = $this->snapshots->captureRollbackSnapshots($case->fresh(), ['correction_process_id' => $correctionProcessId, 'correction_entity_id' => $correctionEntityId]);
            $externalPlan = $this->externalTransactions->buildPlan($case, $plan);
            $queuedReversals = !empty($externalPlan) ? $this->externalTransactions->queueReversalActions($case, $actor) : 0;

            $this->integration->promoteCaseLifecycle($case, config('titan-rewind.process_bridge.rolled_back_state', 'rolled-back'), $actor, [
                'correction_process_id' => $correctionProcessId,
                'correction_entity_id' => $correctionEntityId,
                'reused' => $reused,
                'reissued' => $reissued,
            ]);
            $this->integration->emitPulseHooks($case, 'completed', [
                'correction_process_id' => $correctionProcessId,
                'reused' => $reused,
                'reissued' => $reissued,
                'snapshot_count' => $snapshotCount,
            ]);

            $this->audit->appendEvent([
                'company_id' => $case->company_id,
                'team_id' => $case->team_id,
                'user_id' => $case->user_id,
                'case_id' => $case->id,
                'event_type' => 'rollback_completed',
                'entity_type' => $case->entity_type,
                'entity_id' => $case->entity_id,
                'actor_type' => $actor['type'] ?? 'user',
                'actor_id' => $actor['id'] ?? null,
                'payload_json' => [
                    'correction_process_id' => $correctionProcessId,
                    'reused' => $reused,
                    'reissued' => $reissued,
                    'snapshot_count' => $snapshotCount,
                ],
                'idempotency_key' => 'rollback_completed:' . $case->id . ':' . $correctionProcessId,
            ]);

            $notice = $this->notifications->queueCaseNotification(
                $case,
                'rewind-rollback-complete',
                'Rollback completed. Downstream items were either resumed or marked for reissue.',
                ['correction_process_id' => $correctionProcessId, 'reused' => $reused, 'reissued' => $reissued, 'plan' => $plan]
            );

            return [
                'status' => 'rollback-complete',
                'case_id' => $case->id,
                'correction_process_id' => $correctionProcessId,
                'reused_processes' => $reused,
                'reissued_processes' => $reissued,
                'rollback_plan' => $plan,
                'snapshot_count' => $snapshotCount,
                'notifications' => $notice,
                'external_transactions' => $externalPlan,
                'queued_reversals' => $queuedReversals,
            ];
        });
    }
}
