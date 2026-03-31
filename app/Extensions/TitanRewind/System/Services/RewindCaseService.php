<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;
use App\Extensions\TitanRewind\System\Models\RewindCase;

class RewindCaseService
{
    public function openCase(array $data): RewindCase
    {
        return DB::transaction(fn () => RewindCase::query()->create([
            'company_id' => $data['company_id'],
            'team_id' => $data['team_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'title' => $data['title'] ?? 'Rewind case opened',
            'status' => $data['status'] ?? 'open',
            'severity' => $data['severity'] ?? 'medium',
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'process_id' => $data['process_id'] ?? null,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'detected_at' => $data['detected_at'] ?? now(),
            'meta_json' => $data['meta_json'] ?? [],
        ]));
    }

    public function resolveCase(RewindCase $case, array $actor): RewindCase
    {
        $case->status = 'resolved';
        $case->resolved_at = now();
        $case->resolved_by_type = $actor['type'] ?? 'user';
        $case->resolved_by_id = $actor['id'] ?? null;
        $case->save();
        return $case;
    }
}
