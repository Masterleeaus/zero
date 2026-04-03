<?php

namespace App\Titan\Signals;

use App\Events\TitanCore\TitanActivityEvent;
use Illuminate\Support\Facades\DB;

class AuditTrail
{
    /**
     * Record an audit entry.
     *
     * Extended for Phase 5.10: supports company_id, user_id, intent, signal_uuid,
     * and provider fields so no AI execution is silent.
     *
     * @param  array<string, mixed>  $details
     */
    public function recordEntry(
        string $processId,
        string $action,
        array $details = [],
        ?string $signalId = null,
        ?int $performedBy = null,
    ): void {
        DB::table('tz_audit_log')->insert([
            'process_id'  => $processId,
            'signal_id'   => $signalId,
            'action'      => $action,
            'performed_by' => $performedBy,
            'details'     => json_encode($details, JSON_UNESCAPED_UNICODE),
            'created_at'  => now(),
        ]);
    }

    /**
     * Record a rich AI activity entry (Phase 5.10).
     *
     * Every AI completion, memory write, skill execution, signal dispatch,
     * approval gate, and rewind correction must pass through this method.
     *
     * @param  array<string, mixed>  $context  Must include: company_id, user_id, intent, provider
     */
    public function recordActivity(
        string $processId,
        string $action,
        array $context = [],
        ?string $signalId = null,
    ): void {
        $details = array_merge([
            'company_id'  => null,
            'user_id'     => null,
            'intent'      => null,
            'signal_uuid' => $signalId,
            'provider'    => null,
            'timestamp'   => now()->toIso8601String(),
        ], $context);

        $this->recordEntry($processId, $action, $details, $signalId, $context['user_id'] ?? null);

        // Broadcast to the real-time activity feed (Phase 5.6)
        try {
            TitanActivityEvent::dispatch([
                'event_type' => $action,
                'company_id' => $details['company_id'],
                'user_id'    => $details['user_id'],
                'intent'     => $details['intent'],
                'provider'   => $details['provider'],
                'tokens'     => $details['tokens'] ?? null,
                'duration'   => $details['duration'] ?? null,
                'status'     => $details['status'] ?? 'ok',
            ]);
        } catch (\Throwable $e) {
            // Non-fatal: broadcasting may not be configured
            \Illuminate\Support\Facades\Log::debug('TitanActivityEvent broadcast failed: ' . $e->getMessage());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHistory(string $processId): array
    {
        return DB::table('tz_audit_log')
            ->where('process_id', $processId)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function timeline(string $processId): array
    {
        return [
            'process' => (array) (DB::table('tz_processes')->where('id', $processId)->first() ?? []),
            'states'  => DB::table('tz_process_states')->where('process_id', $processId)->orderBy('created_at')->get()->map(fn ($row) => (array) $row)->all(),
            'audit'   => $this->getHistory($processId),
        ];
    }
}
