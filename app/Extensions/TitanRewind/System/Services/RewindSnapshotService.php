<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Models\RewindSnapshot;
use Illuminate\Support\Facades\DB;

class RewindSnapshotService
{
    public function captureInitiationSnapshots(RewindCase $case, array $impact = []): int
    {
        $count = 0;

        $count += $this->captureRoot($case, 'before', [
            'reason' => data_get($case->meta_json, 'reason'),
            'impact_summary' => [
                'downstream' => count($impact['downstream'] ?? []),
                'users' => count($impact['affected_users'] ?? []),
                'entities' => count($impact['created_entities'] ?? []),
            ],
        ]);

        foreach (($impact['downstream'] ?? []) as $index => $item) {
            $count += $this->captureLinkEntity($case, $item, 'before', [
                'position' => $index,
                'action_required' => $item['action_required'] ?? null,
                'must_reissue' => (bool) ($item['must_reissue'] ?? false),
                'can_reuse' => (bool) ($item['can_reuse'] ?? true),
                'relationship_type' => $item['relationship_type'] ?? null,
            ]);
        }

        return $count;
    }

    public function captureRollbackSnapshots(RewindCase $case, array $options = []): int
    {
        $count = 0;

        $count += $this->captureRoot($case, 'after', [
            'rollback_completed_at' => optional($case->rollback_completed_at)->toIso8601String(),
            'replacement_process_id' => $case->replacement_process_id,
            'correction_process_id' => $case->correction_process_id,
        ], $options['correction_entity_id'] ?? null, $options['correction_process_id'] ?? null);

        $links = DB::table('tz_rewind_links')
            ->where('company_id', $case->company_id)
            ->where('case_id', $case->id)
            ->get();

        foreach ($links as $link) {
            $meta = is_string($link->meta_json) ? (json_decode($link->meta_json, true) ?: []) : ((array) ($link->meta_json ?? []));
            $count += $this->captureLinkEntity($case, [
                'child_process_id' => $link->child_process_id,
                'child_entity_type' => $link->child_entity_type,
                'child_entity_id' => $link->child_entity_id,
                'relationship_type' => $link->relationship_type,
                'action_required' => $link->action_required,
                'status' => $link->status,
                'must_reissue' => (bool) ($link->must_reissue ?? false),
                'can_reuse' => (bool) ($link->can_reuse ?? true),
                'link_id' => $link->id,
            ], 'after', array_merge($meta, [
                'status' => $link->status,
                'held_reason' => $link->held_reason,
                'action_required' => $link->action_required,
                'must_reissue' => (bool) ($link->must_reissue ?? false),
                'can_reuse' => (bool) ($link->can_reuse ?? true),
            ]));
        }

        return $count;
    }

    public function snapshotsForCase(RewindCase $case): array
    {
        if (!DB::getSchemaBuilder()->hasTable('tz_rewind_snapshots')) {
            return [];
        }

        return RewindSnapshot::query()
            ->where('company_id', $case->company_id)
            ->where('case_id', $case->id)
            ->orderBy('captured_at')
            ->orderBy('id')
            ->get()
            ->map(fn (RewindSnapshot $snapshot) => [
                'id' => $snapshot->id,
                'snapshot_key' => $snapshot->snapshot_key,
                'snapshot_stage' => $snapshot->snapshot_stage,
                'snapshot_scope' => $snapshot->snapshot_scope,
                'process_id' => $snapshot->process_id,
                'entity_type' => $snapshot->entity_type,
                'entity_id' => $snapshot->entity_id,
                'link_id' => $snapshot->link_id,
                'source_table' => $snapshot->source_table,
                'source_pk' => $snapshot->source_pk,
                'captured_at' => optional($snapshot->captured_at)->toIso8601String(),
                'before_json' => $snapshot->before_json,
                'after_json' => $snapshot->after_json,
                'meta_json' => $snapshot->meta_json,
            ])
            ->all();
    }

    private function captureRoot(RewindCase $case, string $stage, array $meta = [], ?string $entityIdOverride = null, ?string $processIdOverride = null): int
    {
        $entityType = $case->entity_type;
        $entityId = $entityIdOverride ?: $case->entity_id;
        $processId = $processIdOverride ?: $case->process_id;
        $row = $this->loadEntityRow($case->company_id, $entityType, $entityId, $processId);

        return $this->upsertSnapshot([
            'company_id' => $case->company_id,
            'team_id' => $case->team_id,
            'user_id' => $case->user_id,
            'case_id' => $case->id,
            'snapshot_key' => $stage . ':root:' . ($processId ?: ($entityType . ':' . $entityId)),
            'snapshot_stage' => $stage,
            'snapshot_scope' => 'root',
            'process_id' => $processId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'source_table' => $row['source_table'] ?? null,
            'source_pk' => isset($row['row']->id) ? (string) $row['row']->id : $entityId,
            'before_json' => $stage === 'before' ? $this->normaliseRow($row['row'] ?? null) : null,
            'after_json' => $stage === 'after' ? $this->normaliseRow($row['row'] ?? null) : null,
            'meta_json' => $meta,
            'captured_at' => now(),
        ]);
    }

    private function captureLinkEntity(RewindCase $case, array $item, string $stage, array $meta = []): int
    {
        $entityType = $item['child_entity_type'] ?? null;
        $entityId = $item['child_entity_id'] ?? null;
        $processId = $item['child_process_id'] ?? null;
        if (!$entityType && !$processId) {
            return 0;
        }

        $row = $this->loadEntityRow($case->company_id, $entityType, $entityId, $processId);

        return $this->upsertSnapshot([
            'company_id' => $case->company_id,
            'team_id' => $case->team_id,
            'user_id' => $case->user_id,
            'case_id' => $case->id,
            'snapshot_key' => $stage . ':link:' . ($processId ?: ($entityType . ':' . $entityId)),
            'snapshot_stage' => $stage,
            'snapshot_scope' => 'downstream',
            'process_id' => $processId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'link_id' => $item['link_id'] ?? null,
            'source_table' => $row['source_table'] ?? null,
            'source_pk' => isset($row['row']->id) ? (string) $row['row']->id : $entityId,
            'before_json' => $stage === 'before' ? $this->normaliseRow($row['row'] ?? null) : null,
            'after_json' => $stage === 'after' ? $this->normaliseRow($row['row'] ?? null) : null,
            'meta_json' => $meta,
            'captured_at' => now(),
        ]);
    }

    private function loadEntityRow(int $companyId, ?string $entityType, mixed $entityId, ?string $processId): array
    {
        if ($processId && DB::getSchemaBuilder()->hasTable('tz_processes')) {
            $process = DB::table('tz_processes')
                ->where('company_id', $companyId)
                ->where('process_id', $processId)
                ->first();
            if ($process) {
                return ['source_table' => 'tz_processes', 'row' => $process];
            }
        }

        if ($entityType) {
            $table = $this->entityTable($entityType);
            if ($table && DB::getSchemaBuilder()->hasTable($table) && $entityId !== null) {
                $query = DB::table($table);
                if (DB::getSchemaBuilder()->hasColumn($table, 'company_id')) {
                    $query->where('company_id', $companyId);
                }
                $row = $query->where('id', $entityId)->first();
                if ($row) {
                    return ['source_table' => $table, 'row' => $row];
                }
            }
        }

        return ['source_table' => null, 'row' => null];
    }

    private function entityTable(?string $entityType): ?string
    {
        if (!$entityType) {
            return null;
        }

        return match ($entityType) {
            'quotes', 'service_jobs', 'checklists', 'service_issues', 'invoices', 'payments', 'sites', 'customer_details', 'attendances', 'employee_shifts', 'employee_shift_schedules', 'leaves' => $entityType,
            default => $entityType,
        };
    }

    private function normaliseRow(mixed $row): ?array
    {
        if (!$row) {
            return null;
        }

        return json_decode(json_encode($row), true);
    }

    private function upsertSnapshot(array $attributes): int
    {
        if (!DB::getSchemaBuilder()->hasTable('tz_rewind_snapshots')) {
            return 0;
        }

        RewindSnapshot::query()->updateOrCreate(
            [
                'company_id' => $attributes['company_id'],
                'case_id' => $attributes['case_id'],
                'snapshot_key' => $attributes['snapshot_key'],
            ],
            $attributes
        );

        return 1;
    }
}
