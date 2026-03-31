<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;
use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Models\RewindConflict;

class RewindResolutionService
{
    public function __construct(private readonly RewindAuditService $audit)
    {
    }

    public function resolveConflict(RewindCase $case, RewindConflict $conflict, array $actor, string $resolution, array $notes = []): RewindConflict
    {
        return DB::transaction(function () use ($case, $conflict, $actor, $resolution, $notes) {
            $details = $conflict->details_json ?? [];
            $details['resolution'] = [
                'resolution' => $resolution,
                'notes' => $notes,
                'resolved_by' => $actor,
                'resolved_at' => now()->toIso8601String(),
            ];

            $conflict->status = $resolution === 'rejected' ? 'rejected' : 'resolved';
            $conflict->details_json = $details;
            $conflict->resolved_at = now();
            $conflict->resolved_by_type = $actor['type'] ?? 'user';
            $conflict->resolved_by_id = $actor['id'] ?? null;
            $conflict->save();

            $openCritical = RewindConflict::query()
                ->where('company_id', $case->company_id)
                ->where('case_id', $case->id)
                ->where('severity', 'critical')
                ->where('status', 'open')
                ->count();

            if ($openCritical === 0 && $case->status === 'conflict-hold') {
                $case->status = 'awaiting-correction';
                $case->save();
            }

            $this->audit->appendEvent([
                'company_id' => $case->company_id,
                'team_id' => $case->team_id,
                'user_id' => $case->user_id,
                'case_id' => $case->id,
                'event_type' => 'conflict_resolved',
                'entity_type' => $case->entity_type,
                'entity_id' => $case->entity_id,
                'actor_type' => $actor['type'] ?? 'user',
                'actor_id' => $actor['id'] ?? null,
                'payload_json' => [
                    'conflict_id' => $conflict->id,
                    'conflict_type' => $conflict->conflict_type,
                    'resolution' => $resolution,
                    'notes' => $notes,
                ],
                'idempotency_key' => 'conflict_resolved:' . $case->id . ':' . $conflict->id . ':' . $resolution,
            ]);

            return $conflict;
        });
    }
}
