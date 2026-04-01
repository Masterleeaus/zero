<?php

namespace App\Titan\Signals;

use Illuminate\Support\Arr;

class SignalNormalizer
{
    public function normalize(array $signal): array
    {
        $companyId = $signal['company_id']
            ?? Arr::get($signal, 'company.id')
            ?? Arr::get($signal, 'payload.company_id');

        $teamId = $signal['team_id']
            ?? Arr::get($signal, 'team.id')
            ?? Arr::get($signal, 'payload.team_id');

        $userId = $signal['user_id']
            ?? Arr::get($signal, 'user.id')
            ?? Arr::get($signal, 'payload.user_id');

        $payload = (array) ($signal['payload'] ?? Arr::except($signal, ['payload']));

        return [
            'id' => $signal['id'] ?? ('sig-'.str_replace('.', '-', uniqid('', true))),
            'type' => $signal['type'] ?? Arr::get($signal, 'action', 'generic'),
            'kind' => $signal['kind'] ?? Arr::get($signal, 'entity_type', 'generic'),
            'severity' => $signal['severity'] ?? SignalSeverity::AMBER,
            'title' => $signal['title'] ?? ucfirst(str_replace('.', ' ', (string) ($signal['type'] ?? 'signal'))),
            'body' => $signal['body'] ?? null,
            'source' => $signal['source'] ?? 'unknown',
            'origin' => $signal['origin'] ?? 'server',
            'company_id' => $companyId,
            'team_id' => $teamId,
            'user_id' => $userId,
            'process_id' => $signal['process_id'] ?? Arr::get($signal, 'meta.process_id'),
            'status' => $signal['status'] ?? 'normalized',
            'validation_result' => $signal['validation_result'] ?? null,
            'validation_errors' => (array) ($signal['validation_errors'] ?? []),
            'validation_warnings' => (array) ($signal['validation_warnings'] ?? []),
            'approval_chain' => (array) ($signal['approval_chain'] ?? []),
            'next_approver_id' => $signal['next_approver_id'] ?? null,
            'approved_by' => (array) ($signal['approved_by'] ?? []),
            'processed_entity_id' => $signal['processed_entity_id'] ?? null,
            'processed_at' => $signal['processed_at'] ?? null,
            'rewind_from' => $signal['rewind_from'] ?? null,
            'rolled_back_by' => $signal['rolled_back_by'] ?? null,
            'payload' => $payload,
            'meta' => (array) ($signal['meta'] ?? []),
            'timestamp' => $signal['timestamp'] ?? now()->toIso8601String(),
            'source_engine' => $signal['source_engine'] ?? $signal['source'] ?? 'unknown',
        ];
    }
}
