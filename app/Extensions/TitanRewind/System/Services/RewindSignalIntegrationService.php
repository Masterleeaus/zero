<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use Illuminate\Support\Facades\DB;

class RewindSignalIntegrationService
{
    public function __construct(private readonly RewindAuditService $audit)
    {
    }

    public function canInitiateFromProcess(int $companyId, ?string $processId): bool
    {
        if (!$processId || !DB::getSchemaBuilder()->hasTable(config('titan-rewind.process_bridge.process_table', 'tz_processes'))) {
            return true;
        }

        $process = DB::table(config('titan-rewind.process_bridge.process_table', 'tz_processes'))
            ->where('company_id', $companyId)
            ->where('id', $processId)
            ->first();

        if (!$process) {
            return false;
        }

        return in_array((string) ($process->current_state ?? ''), config('titan-rewind.process_bridge.allowed_rewind_from_states', []), true);
    }

    public function promoteCaseLifecycle(RewindCase $case, string $targetState, array $actor = [], array $meta = []): void
    {
        $processTable = config('titan-rewind.process_bridge.process_table', 'tz_processes');
        $stateTable = config('titan-rewind.process_bridge.state_table', 'tz_process_states');

        if (!$case->process_id || !DB::getSchemaBuilder()->hasTable($processTable)) {
            return;
        }

        $process = DB::table($processTable)
            ->where('company_id', $case->company_id)
            ->where('id', $case->process_id)
            ->first();

        if (!$process) {
            return;
        }

        DB::table($processTable)
            ->where('company_id', $case->company_id)
            ->where('id', $case->process_id)
            ->update([
                'current_state' => $targetState,
                'updated_at' => now(),
            ]);

        if (DB::getSchemaBuilder()->hasTable($stateTable)) {
            DB::table($stateTable)->insert([
                'process_id' => $case->process_id,
                'from_state' => $process->current_state ?? null,
                'to_state' => $targetState,
                'metadata' => json_encode(array_merge($meta, [
                    'source' => 'titan_rewind',
                    'case_id' => $case->id,
                    'actor' => $actor,
                ])),
                'created_at' => now(),
            ]);
        }
    }

    public function emitPulseHooks(RewindCase $case, string $phase, array $payload = []): void
    {
        if (!config('titan-rewind.pulse.enabled', true)) {
            return;
        }

        DB::table('titan_rewind_actions')->insert([
            'company_id' => $case->company_id,
            'team_id' => $case->team_id,
            'user_id' => $case->user_id,
            'case_id' => $case->id,
            'fix_id' => null,
            'action_type' => config('titan-rewind.pulse.queue_action_type', 'pulse.rollback.queued'),
            'target_type' => 'pulse',
            'target_id' => $phase,
            'before_json' => null,
            'after_json' => json_encode([
                'signal_type' => config('titan-rewind.pulse.states.' . $phase, 'rewind.unknown'),
                'case_id' => $case->id,
                'process_id' => $case->process_id,
                'payload' => $payload,
                'queued_at' => now()->toIso8601String(),
            ]),
            'executed_by_type' => 'system',
            'executed_by_id' => null,
            'executed_at' => now(),
            'success' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $signalTable = config('titan-rewind.process_bridge.signal_table', 'tz_signals');
        if (DB::getSchemaBuilder()->hasTable($signalTable)) {
            DB::table($signalTable)->insert([
                'id' => 'rewind-' . $case->id . '-' . str_replace('.', '-', $phase) . '-' . now()->timestamp,
                'company_id' => $case->company_id,
                'team_id' => $case->team_id,
                'user_id' => $case->user_id,
                'process_id' => $case->process_id,
                'type' => config('titan-rewind.pulse.states.' . $phase, 'rewind.unknown'),
                'kind' => 'rewind',
                'severity' => $case->severity ?? 'high',
                'source' => 'titan_rewind',
                'origin' => 'rewind_engine',
                'payload' => json_encode(array_merge([
                    'case_id' => $case->id,
                    'entity_type' => $case->entity_type,
                    'entity_id' => $case->entity_id,
                ], $payload)),
                'meta' => json_encode(['phase' => $phase]),
                'status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function autoInitiateCandidates(int $companyId, int $limit = 25): array
    {
        if (!config('titan-rewind.signal_integration.auto_initiate_from_signal', true)) {
            return [];
        }

        $signalTable = config('titan-rewind.process_bridge.signal_table', 'tz_signals');
        if (!DB::getSchemaBuilder()->hasTable($signalTable)) {
            return [];
        }

        $signals = DB::table($signalTable)
            ->where('company_id', $companyId)
            ->whereIn('type', config('titan-rewind.signal_integration.rewind_trigger_types', []))
            ->whereIn('status', config('titan-rewind.signal_integration.root_signal_statuses', ['new']))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $signals->map(fn ($row) => [
            'process_id' => $row->process_id ?? null,
            'signal_id' => $row->id ?? null,
            'entity_type' => data_get($this->decode($row->payload ?? null), 'entity_type'),
            'entity_id' => data_get($this->decode($row->payload ?? null), 'entity_id'),
            'reason' => data_get($this->decode($row->payload ?? null), 'reason', 'Signal requested rewind.'),
        ])->all();
    }

    private function decode(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }
}
