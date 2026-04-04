<?php

namespace App\Titan\Signals;

use Illuminate\Support\Facades\DB;

class AuditTrail
{
    public function recordEntry(string $processId, string $action, array $details = [], ?string $signalId = null, ?int $performedBy = null): void
    {
        DB::table('tz_audit_log')->insert([
            'process_id' => $processId,
            'signal_id' => $signalId,
            'action' => $action,
            'performed_by' => $performedBy,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }

    public function getHistory(string $processId): array
    {
        return DB::table('tz_audit_log')
            ->where('process_id', $processId)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function timeline(string $processId): array
    {
        return [
            'process' => (array) (DB::table('tz_processes')->where('id', $processId)->first() ?? []),
            'states' => DB::table('tz_process_states')->where('process_id', $processId)->orderBy('created_at')->get()->map(fn ($row) => (array) $row)->all(),
            'audit' => $this->getHistory($processId),
        ];
    }
}
