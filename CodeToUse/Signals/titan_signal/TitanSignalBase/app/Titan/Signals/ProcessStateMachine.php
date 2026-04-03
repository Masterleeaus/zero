<?php

namespace App\Titan\Signals;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessStateMachine
{
    private const VALID = [
        'initiated' => ['signal-queued', 'cancelled'],
        'signal-queued' => ['awaiting-validation', 'cancelled'],
        'awaiting-validation' => ['validation-approved', 'validation-rejected', 'conflict-hold'],
        'validation-approved' => ['awaiting-processing', 'processing'],
        'awaiting-processing' => ['processing', 'processing-rejected', 'awaiting-approval'],
        'awaiting-approval' => ['processing', 'approval-rejected', 'awaiting-more-info'],
        'processing' => ['processed', 'processing-error', 'processing-hold'],
        'processed' => ['rewinding'],
        'validation-rejected' => ['initiated'],
        'approval-rejected' => ['initiated'],
    ];

    public function __construct(
        protected ?AuditTrail $auditTrail = null,
    ) {
        $this->auditTrail ??= app(AuditTrail::class);
    }

    public function assertValid(string $from, string $to): void
    {
        $allowed = self::VALID[$from] ?? [];
        if (! in_array($to, $allowed, true)) {
            throw new InvalidArgumentException("Invalid transition: {$from} → {$to}");
        }
    }

    public function transitionState(string $processId, string $newState, array $metadata = []): array
    {
        $process = DB::table('tz_processes')->where('id', $processId)->first();
        if (! $process) {
            throw new InvalidArgumentException("Unknown process: {$processId}");
        }

        $fromState = (string) $process->current_state;
        $this->assertValid($fromState, $newState);

        DB::table('tz_process_states')->insert([
            'process_id' => $processId,
            'from_state' => $fromState,
            'to_state' => $newState,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        DB::table('tz_processes')->where('id', $processId)->update([
            'current_state' => $newState,
            'updated_at' => now(),
        ]);

        $stateSignalId = 'sig-state-'.str_replace('.', '-', uniqid('', true));
        DB::table('tz_signal_queue')->insert([
            'signal_id' => $stateSignalId,
            'payload' => json_encode([
                'id' => $stateSignalId,
                'type' => 'process.state-changed',
                'kind' => 'process',
                'severity' => SignalSeverity::AMBER,
                'company_id' => $process->company_id,
                'team_id' => $process->team_id,
                'user_id' => $process->user_id,
                'process_id' => $processId,
                'payload' => [
                    'from_state' => $fromState,
                    'to_state' => $newState,
                    'metadata' => $metadata,
                ],
                'meta' => ['system_generated' => true],
                'status' => 'queued',
                'timestamp' => now()->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE),
            'broadcast_status' => 'pending',
            'retry_count' => 0,
            'created_at' => now(),
        ]);

        $this->auditTrail->recordEntry($processId, 'process.state_changed', [
            'from' => $fromState,
            'to' => $newState,
            'metadata' => $metadata,
        ], null, isset($process->user_id) ? (int) $process->user_id : null);

        return [
            'process_id' => $processId,
            'from_state' => $fromState,
            'to_state' => $newState,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function transitions(): array
    {
        return self::VALID;
    }
}
