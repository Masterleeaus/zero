<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;
use App\Extensions\TitanRewind\System\Models\RewindCase;

class RewindNotificationService
{
    public function buildAudience(RewindCase $case, array $impact = []): array
    {
        $users = collect([$case->user_id])->filter();

        foreach (($impact['affected_users'] ?? []) as $userId) {
            $users->push($userId);
        }

        $approvers = data_get($case->meta_json, 'approval.approved_by', []);
        foreach ($approvers as $userId) {
            $users->push($userId);
        }

        return $users->filter()->unique()->values()->all();
    }

    public function queueCaseNotification(RewindCase $case, string $type, string $message, array $payload = []): array
    {
        $audience = $this->buildAudience($case, $payload['impact'] ?? []);

        $rows = collect($audience)->map(fn ($userId) => [
            'company_id' => $case->company_id,
            'team_id' => $case->team_id,
            'user_id' => $userId,
            'case_id' => $case->id,
            'fix_id' => null,
            'action_type' => 'notification.queued',
            'target_type' => 'user',
            'target_id' => (string) $userId,
            'before_json' => null,
            'after_json' => [
                'type' => $type,
                'message' => $message,
                'payload' => $payload,
                'delivery_status' => 'queued',
            ],
            'executed_by_type' => 'system',
            'executed_by_id' => null,
            'executed_at' => now(),
            'success' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if (!empty($rows)) {
            DB::table('titan_rewind_actions')->insert(array_map(function (array $row) {
                $row['after_json'] = json_encode($row['after_json']);
                return $row;
            }, $rows));
        }

        return [
            'audience' => $audience,
            'count' => count($audience),
            'type' => $type,
            'message' => $message,
        ];
    }

    public function flushQueuedNotifications(int $limit = 50): int
    {
        $rows = DB::table('titan_rewind_actions')
            ->where('action_type', 'notification.queued')
            ->where('success', true)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $processed = 0;
        foreach ($rows as $row) {
            $after = is_string($row->after_json) ? (json_decode($row->after_json, true) ?: []) : (array) $row->after_json;
            if (($after['delivery_status'] ?? null) === 'dispatched') {
                continue;
            }
            $after['delivery_status'] = 'dispatched';
            $after['dispatched_at'] = now()->toIso8601String();
            DB::table('titan_rewind_actions')->where('id', $row->id)->update([
                'after_json' => json_encode($after),
                'updated_at' => now(),
            ]);
            $processed++;
        }

        return $processed;
    }
}
