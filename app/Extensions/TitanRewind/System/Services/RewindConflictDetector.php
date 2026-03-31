<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;
use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Models\RewindConflict;

class RewindConflictDetector
{
    public function detectForCase(RewindCase $case, array $correctionData = []): array
    {
        $conflicts = [];

        $entityTable = $case->entity_type;
        if ($entityTable && $case->entity_id && DB::getSchemaBuilder()->hasTable($entityTable)) {
            $entity = DB::table($entityTable)->where('id', $case->entity_id)->first();
            $entityUpdatedAt = $entity->updated_at ?? $entity->last_modified_at ?? null;
            if ($entityUpdatedAt && $case->detected_at && strtotime((string) $entityUpdatedAt) > $case->detected_at->getTimestamp()) {
                $conflicts[] = [
                    'conflict_type' => 'entity-modified',
                    'severity' => 'high',
                    'message' => 'Entity changed after the original process and may require manual merge.',
                    'details_json' => [
                        'entity_type' => $case->entity_type,
                        'entity_id' => $case->entity_id,
                        'updated_at' => (string) $entityUpdatedAt,
                    ],
                    'resolution_hint' => 'Review current entity state before applying correction.',
                ];
            }
        }

        $deepCascadeCount = DB::table('tz_rewind_links')
            ->where('company_id', $case->company_id)
            ->whereIn('parent_process_id', function ($query) use ($case) {
                $query->select('child_process_id')
                    ->from('tz_rewind_links')
                    ->where('company_id', $case->company_id)
                    ->where('case_id', $case->id);
            })->count();

        if ($deepCascadeCount > 0) {
            $conflicts[] = [
                'conflict_type' => 'deep-cascade',
                'severity' => $deepCascadeCount > 3 ? 'critical' : 'high',
                'message' => 'Downstream processes have their own downstream dependants.',
                'details_json' => ['affected_count' => $deepCascadeCount],
                'resolution_hint' => 'Use staged rollback or manual review for grandchild processes.',
            ];
        }

        if ($case->entity_type === 'payments') {
            $conflicts[] = [
                'conflict_type' => 'external-transaction',
                'severity' => 'critical',
                'message' => 'Payment rewind may require refund and reissue handling.',
                'details_json' => ['entity_type' => 'payments', 'entity_id' => $case->entity_id],
                'resolution_hint' => 'Refund external payment first, then create corrected payment process.',
            ];
        }

        if (!empty($correctionData) && isset($correctionData['company_id']) && (int) $correctionData['company_id'] !== (int) $case->company_id) {
            $conflicts[] = [
                'conflict_type' => 'tenant-mismatch',
                'severity' => 'critical',
                'message' => 'Correction attempted against a different tenant boundary.',
                'details_json' => ['expected_company_id' => $case->company_id, 'provided_company_id' => $correctionData['company_id']],
                'resolution_hint' => 'Submit correction under the same company_id as the original case.',
            ];
        }

        if (!empty($correctionData) && empty($correctionData['fields_changed'] ?? null) && empty($correctionData['patch'] ?? null)) {
            $conflicts[] = [
                'conflict_type' => 'empty-correction',
                'severity' => 'high',
                'message' => 'Correction payload does not clearly describe the change set.',
                'details_json' => ['keys' => array_keys($correctionData)],
                'resolution_hint' => 'Provide fields_changed or patch information for safer rollback handling.',
            ];
        }

        $existingOpenCritical = RewindConflict::query()
            ->where('company_id', $case->company_id)
            ->where('case_id', $case->id)
            ->where('severity', 'critical')
            ->where('status', 'open')
            ->count();

        if ($existingOpenCritical > 0) {
            $conflicts[] = [
                'conflict_type' => 'unresolved-critical-conflicts',
                'severity' => 'critical',
                'message' => 'Case still has unresolved critical conflicts.',
                'details_json' => ['count' => $existingOpenCritical],
                'resolution_hint' => 'Resolve critical conflicts before completing rollback.',
            ];
        }

        return $conflicts;
    }

    public function persistForCase(RewindCase $case, array $conflicts): array
    {
        $records = [];
        foreach ($conflicts as $conflict) {
            $records[] = RewindConflict::query()->firstOrCreate([
                'company_id' => $case->company_id,
                'case_id' => $case->id,
                'conflict_type' => $conflict['conflict_type'],
                'entity_type' => $case->entity_type,
                'entity_id' => $case->entity_id,
            ], [
                'team_id' => $case->team_id,
                'user_id' => $case->user_id,
                'process_id' => $case->process_id,
                'severity' => $conflict['severity'] ?? 'high',
                'status' => 'open',
                'message' => $conflict['message'] ?? 'Conflict detected.',
                'details_json' => $conflict['details_json'] ?? [],
                'resolution_hint' => $conflict['resolution_hint'] ?? null,
            ]);
        }

        return $records;
    }
}
