<?php

namespace App\Titan\Signals;

use Illuminate\Support\Facades\DB;

class ProcessRecorder
{
    public function __construct(
        protected ?ProcessStateMachine $stateMachine = null,
        protected ?AuditTrail $auditTrail = null,
    ) {
        $this->stateMachine ??= app(ProcessStateMachine::class);
        $this->auditTrail ??= app(AuditTrail::class);
    }

    public function record(array $payload): array
    {
        $processId = $payload['process_id'] ?? ('proc-'.str_replace('.', '-', uniqid('', true)));
        $now = now();

        $process = [
            'id' => $processId,
            'company_id' => $payload['company_id'] ?? null,
            'team_id' => $payload['team_id'] ?? null,
            'user_id' => $payload['user_id'] ?? null,
            'entity_type' => $payload['entity_type'] ?? 'generic',
            'domain' => $payload['domain'] ?? 'general',
            'originating_node' => $payload['originating_node'] ?? 'server',
            'current_state' => $payload['current_state'] ?? 'initiated',
            'signal_id' => $payload['signal_id'] ?? null,
            'data' => json_encode($payload['data'] ?? [], JSON_UNESCAPED_UNICODE),
            'context' => json_encode($payload['context'] ?? [], JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('tz_processes')->updateOrInsert(['id' => $processId], $process);
        DB::table('tz_process_states')->insert([
            'process_id' => $processId,
            'from_state' => null,
            'to_state' => $process['current_state'],
            'metadata' => json_encode(['created' => true, 'originating_node' => $process['originating_node']], JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
        ]);

        $this->auditTrail->recordEntry($processId, 'process.recorded', [
            'entity_type' => $process['entity_type'],
            'domain' => $process['domain'],
            'originating_node' => $process['originating_node'],
        ], $process['signal_id'], $process['user_id']);

        return [
            'status' => 'recorded',
            'process_id' => $processId,
            'current_state' => $process['current_state'],
            'timestamp' => $now->toIso8601String(),
        ];
    }

    public function queueSignal(array $signal): array
    {
        DB::table('tz_signal_queue')->updateOrInsert(
            ['signal_id' => $signal['id']],
            [
                'payload' => json_encode($signal, JSON_UNESCAPED_UNICODE),
                'broadcast_status' => 'pending',
                'retry_count' => 0,
                'created_at' => now(),
            ]
        );

        if (! empty($signal['process_id'])) {
            $this->stateMachine->transitionState($signal['process_id'], 'signal-queued', [
                'signal_id' => $signal['id'],
                'queued' => true,
            ]);
        }

        return [
            'queued' => true,
            'signal_id' => $signal['id'],
        ];
    }
}
