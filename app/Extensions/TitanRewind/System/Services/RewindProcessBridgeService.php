<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;

class RewindProcessBridgeService
{
    public function snapshot(array $context): array
    {
        $companyId = $context['company_id'] ?? null;
        $processId = $context['process_id'] ?? null;
        $entityType = $context['entity_type'] ?? null;
        $entityId = $context['entity_id'] ?? null;

        $process = null;
        $states = [];
        $signals = [];
        $dependencies = ['parents' => [], 'children' => []];

        if ($companyId && $processId && DB::getSchemaBuilder()->hasTable('tz_processes')) {
            $process = DB::table('tz_processes')
                ->where('company_id', $companyId)
                ->where('process_id', $processId)
                ->first();
        }

        if ($companyId && $processId && DB::getSchemaBuilder()->hasTable('tz_process_states')) {
            $states = DB::table('tz_process_states')
                ->where('company_id', $companyId)
                ->where('process_id', $processId)
                ->orderBy('created_at')
                ->get()
                ->map(fn ($row) => [
                    'from_state' => $row->from_state ?? null,
                    'to_state' => $row->to_state ?? null,
                    'meta_json' => $this->decode($row->meta_json ?? null),
                    'created_at' => (string) ($row->created_at ?? ''),
                ])->all();
        }

        if ($companyId && $processId && DB::getSchemaBuilder()->hasTable('tz_signals')) {
            $signalRows = DB::table('tz_signals')
                ->where('company_id', $companyId)
                ->where('process_id', $processId)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
            $signals = $signalRows->map(fn ($row) => [
                'signal_id' => $row->id ?? null,
                'type' => $row->type ?? null,
                'severity' => $row->severity ?? null,
                'status' => $row->status ?? null,
                'payload' => $this->decode($row->payload ?? null),
                'created_at' => (string) ($row->created_at ?? ''),
            ])->all();
        }

        if ($companyId && $processId && DB::getSchemaBuilder()->hasTable('tz_process_dependencies')) {
            $dependencies['parents'] = DB::table('tz_process_dependencies')
                ->where('company_id', $companyId)
                ->where('child_process_id', $processId)
                ->get()
                ->map(fn ($row) => [
                    'process_id' => $row->parent_process_id,
                    'relationship_type' => $row->relationship_type ?? null,
                ])->all();
            $dependencies['children'] = DB::table('tz_process_dependencies')
                ->where('company_id', $companyId)
                ->where('parent_process_id', $processId)
                ->get()
                ->map(fn ($row) => [
                    'process_id' => $row->child_process_id,
                    'relationship_type' => $row->relationship_type ?? null,
                ])->all();
        } elseif ($companyId && $processId && DB::getSchemaBuilder()->hasTable('tz_processes')) {
            $dependencies['children'] = DB::table('tz_processes')
                ->where('company_id', $companyId)
                ->where(function ($query) use ($processId) {
                    $query->where('created_from_process', $processId)
                        ->orWhere('parent_process_id', $processId);
                })
                ->get()
                ->map(fn ($row) => [
                    'process_id' => $row->process_id,
                    'relationship_type' => 'process-link',
                ])->all();
        }

        $entitySnapshot = $this->entitySnapshot($entityType, $entityId, $companyId);

        return [
            'process' => $process ? (array) $process : null,
            'states' => $states,
            'signals' => $signals,
            'dependencies' => $dependencies,
            'entity_snapshot' => $entitySnapshot,
        ];
    }

    public function entitySnapshot(?string $entityType, mixed $entityId, ?int $companyId): ?array
    {
        if (!$entityType || !$entityId || !$companyId) {
            return null;
        }

        $table = $entityType;
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return null;
        }

        $query = DB::table($table)->where('id', $entityId);
        if (DB::getSchemaBuilder()->hasColumn($table, 'company_id')) {
            $query->where('company_id', $companyId);
        }

        $row = $query->first();
        return $row ? (array) $row : null;
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
