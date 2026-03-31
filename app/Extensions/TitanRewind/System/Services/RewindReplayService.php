<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use Illuminate\Support\Arr;

class RewindReplayService
{
    public function __construct(
        private readonly RewindHistoryService $history,
        private readonly RewindRollbackPlannerService $planner,
        private readonly RewindExternalTransactionService $externalTransactions,
    ) {
    }

    public function replayBundle(RewindCase $case): array
    {
        $history = $this->history->history($case);
        $plan = $history['rollback_plan'] ?? $this->planner->plan($case);
        $timeline = collect($history['timeline'] ?? []);
        $states = collect(data_get($history, 'process_snapshot.states', []));
        $signals = collect(data_get($history, 'process_snapshot.signals', []));
        $conflicts = collect($history['conflicts'] ?? []);

        $sequence = [];
        foreach ($timeline as $event) {
            $sequence[] = [
                'type' => 'audit-event',
                'label' => $event['event_type'] ?? 'event',
                'created_at' => $event['created_at'] ?? null,
                'payload' => $event['payload_json'] ?? [],
            ];
        }
        foreach ($states as $state) {
            $sequence[] = [
                'type' => 'process-state',
                'label' => ($state['from_state'] ?? 'unknown') . '→' . ($state['to_state'] ?? 'unknown'),
                'created_at' => $state['created_at'] ?? null,
                'payload' => $state['meta_json'] ?? [],
            ];
        }
        foreach ($signals as $signal) {
            $sequence[] = [
                'type' => 'signal',
                'label' => $signal['type'] ?? 'signal',
                'created_at' => $signal['created_at'] ?? null,
                'payload' => [
                    'severity' => $signal['severity'] ?? null,
                    'status' => $signal['status'] ?? null,
                ],
            ];
        }

        usort($sequence, function (array $a, array $b): int {
            return strcmp((string) ($a['created_at'] ?? ''), (string) ($b['created_at'] ?? ''));
        });

        $blockingConflicts = $conflicts->filter(fn (array $conflict) => ($conflict['status'] ?? 'open') === 'open' && in_array(($conflict['severity'] ?? null), ['critical', 'high'], true))
            ->values()->all();

        return [
            'case' => Arr::only($history['case'] ?? [], ['id', 'title', 'status', 'severity', 'process_id', 'entity_type', 'entity_id', 'correction_process_id', 'replacement_process_id']),
            'sequence' => $sequence,
            'sequence_counts' => [
                'events' => $timeline->count(),
                'states' => $states->count(),
                'signals' => $signals->count(),
            ],
            'reconciliation' => [
                'current_case_status' => data_get($history, 'case.status'),
                'has_correction_process' => !empty(data_get($history, 'case.correction_process_id')),
                'has_replacement_process' => !empty(data_get($history, 'case.replacement_process_id')),
                'open_blockers' => count($blockingConflicts),
                'can_finalize' => count($blockingConflicts) === 0,
            ],
            'rollback_plan' => $plan,
            'external_transactions' => $this->externalTransactions->buildPlan($case, $plan),
            'blocking_conflicts' => $blockingConflicts,
        ];
    }
}
