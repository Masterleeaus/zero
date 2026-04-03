<?php

declare(strict_types=1);

namespace App\Listeners\Sync;

use App\Events\Sync\EdgeBatchSynced;
use App\Models\Sync\EdgeSyncLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * MODULE 05 — TitanEdgeSync
 *
 * Records each completed sync batch on the trust work ledger when
 * Module 03 (TrustWorkLedger) is available, otherwise logs the event.
 *
 * This listener fires after EdgeBatchSynced and ensures every completed
 * offline sync is durably recorded for trust/audit purposes.
 */
class RecordSyncEventOnTrustLedger implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(EdgeBatchSynced $event): void
    {
        $log = $event->syncLog;

        try {
            // If TrustLedgerService is available (Module 03), delegate to it.
            if (app()->bound(\App\Services\Trust\TrustLedgerService::class)) {
                /** @var \App\Services\Trust\TrustLedgerService $ledger */
                $ledger = app(\App\Services\Trust\TrustLedgerService::class);
                $ledger->recordSyncBatch($log);

                return;
            }

            // Fallback: structured audit log until Module 03 is wired.
            Log::info('edge_sync.batch_complete', [
                'batch_id'         => $log->batch_id,
                'company_id'       => $log->company_id,
                'user_id'          => $log->user_id,
                'device_id'        => $log->device_id,
                'operations_count' => $log->operations_count,
                'conflicts_count'  => $log->conflicts_count,
                'failed_count'     => $log->failed_count,
                'completed_at'     => $log->completed_at?->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('RecordSyncEventOnTrustLedger failed', [
                'batch_id' => $log->batch_id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
