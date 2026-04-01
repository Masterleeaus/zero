<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Extensions\TitanRewind\System\Models\RewindEvent;

class RewindAuditService
{
    public function appendEvent(array $data): RewindEvent
    {
        $data['created_at'] = $data['created_at'] ?? now();

        if (!empty($data['idempotency_key'])) {
            $existing = RewindEvent::query()->where('company_id', $data['company_id'])->where('case_id', $data['case_id'])->where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data) {
            $prev = RewindEvent::query()->where('company_id', $data['company_id'])->where('case_id', $data['case_id'])->orderByDesc('id')->first();
            $payload = [
                'company_id' => $data['company_id'],
                'team_id' => $data['team_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'case_id' => $data['case_id'],
                'event_type' => $data['event_type'] ?? 'event',
                'entity_type' => $data['entity_type'] ?? null,
                'entity_id' => $data['entity_id'] ?? null,
                'actor_type' => $data['actor_type'] ?? 'system',
                'actor_id' => $data['actor_id'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? (string) Str::uuid(),
                'payload_json' => $data['payload_json'] ?? [],
                'prev_event_hash' => $prev?->event_hash,
                'created_at' => $data['created_at'],
            ];
            $payload['event_hash'] = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return RewindEvent::query()->create($payload);
        });
    }

    public function timeline(int $companyId, int $caseId): array
    {
        return RewindEvent::query()
            ->where('company_id', $companyId)
            ->where('case_id', $caseId)
            ->orderBy('id')
            ->get()
            ->map(fn (RewindEvent $event) => [
                'event_type' => $event->event_type,
                'actor_type' => $event->actor_type,
                'actor_id' => $event->actor_id,
                'entity_type' => $event->entity_type,
                'entity_id' => $event->entity_id,
                'payload_json' => $event->payload_json,
                'created_at' => optional($event->created_at)->toIso8601String(),
                'event_hash' => $event->event_hash,
                'prev_event_hash' => $event->prev_event_hash,
            ])->all();
    }
}
