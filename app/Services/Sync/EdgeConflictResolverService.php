<?php

declare(strict_types=1);

namespace App\Services\Sync;

use App\Events\Sync\EdgeConflictResolved;
use App\Models\Sync\EdgeSyncConflict;
use App\Models\Sync\EdgeSyncQueue;
use Illuminate\Support\Facades\Log;

/**
 * MODULE 05 — TitanEdgeSync
 *
 * Handles automatic conflict resolution for edge sync operations.
 *
 * Strategies:
 *  - server_wins  : discard client payload, keep server state
 *  - client_wins  : overwrite server state with client payload
 *  - merge        : shallow merge of client payload into server state
 *  - manual       : mark for human review, do not auto-apply
 */
class EdgeConflictResolverService
{
    public function __construct(
        private readonly EdgeSyncPayloadProcessor $processor,
    ) {}

    /**
     * Attempt automatic resolution using default strategy per operation type.
     *
     * Returns true if resolution was applied; false if manual review required.
     */
    public function autoResolve(EdgeSyncConflict $conflict): bool
    {
        $item     = $conflict->syncQueue;
        $strategy = $this->defaultStrategyFor($item->operation_type);

        if ($strategy === 'manual') {
            Log::info('edge_sync.conflict_requires_manual_review', [
                'conflict_id'    => $conflict->id,
                'operation_type' => $item->operation_type,
            ]);

            return false;
        }

        $this->applyResolution($conflict, $strategy);

        return true;
    }

    /**
     * Apply a specific resolution strategy to a conflict.
     */
    public function applyResolution(EdgeSyncConflict $conflict, string $strategy): void
    {
        $item = $conflict->syncQueue;

        match ($strategy) {
            'server_wins' => $this->applyServerWins($conflict),
            'client_wins' => $this->applyClientWins($conflict, $item),
            'merge'       => $this->applyMerge($conflict, $item),
            'manual'      => null, // no-op — handled by UI
            default       => $this->applyServerWins($conflict),
        };

        if ($strategy !== 'manual') {
            $conflict->resolve('system', [
                'strategy'    => $strategy,
                'resolved_at' => now()->toIso8601String(),
            ]);

            $item->markSynced();

            event(new EdgeConflictResolved($conflict, $strategy));
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function applyServerWins(EdgeSyncConflict $conflict): void
    {
        // Server state is already current — simply discard the client operation.
        Log::info('edge_sync.conflict_server_wins', ['conflict_id' => $conflict->id]);
    }

    private function applyClientWins(EdgeSyncConflict $conflict, EdgeSyncQueue $item): void
    {
        Log::info('edge_sync.conflict_client_wins', ['conflict_id' => $conflict->id]);

        $this->dispatchPayload($item);
    }

    private function applyMerge(EdgeSyncConflict $conflict, EdgeSyncQueue $item): void
    {
        Log::info('edge_sync.conflict_merge', ['conflict_id' => $conflict->id]);

        // Shallow-merge: client payload fields are applied only where server
        // state does not have a non-null value for that field.
        $serverState  = $conflict->server_state ?? [];
        $clientState  = $conflict->client_state ?? [];
        $mergedFields = [];

        foreach ($clientState as $key => $clientValue) {
            $serverValue = $serverState[$key] ?? null;

            // Prefer client value unless server already has a non-null value.
            if ($serverValue === null) {
                $mergedFields[$key] = $clientValue;
            }
        }

        if (! empty($mergedFields)) {
            // Build a merged payload without mutating the original persisted record.
            $mergedPayload = array_merge($item->payload ?? [], $mergedFields);

            // Dispatch processor with a transient clone carrying the merged payload.
            $transient          = clone $item;
            $transient->payload = $mergedPayload;

            $this->dispatchPayload($transient);
        }
    }

    private function dispatchPayload(EdgeSyncQueue $item): void
    {
        match ($item->operation_type) {
            'job_update'           => $this->processor->applyJobUpdate($item),
            'checklist_response'   => $this->processor->applyChecklistResponse($item),
            'inspection_response'  => $this->processor->applyInspectionResponse($item),
            'signature_capture'    => $this->processor->applySignatureCapture($item),
            'job_complete'         => $this->processor->applyJobCompletion($item),
            default                => Log::warning('edge_sync.unknown_operation_type', [
                'type'     => $item->operation_type,
                'queue_id' => $item->id,
            ]),
        };
    }

    private function defaultStrategyFor(string $operationType): string
    {
        return match ($operationType) {
            'checklist_response', 'inspection_response', 'evidence_upload' => 'merge',
            'job_update'                                                    => 'server_wins',
            'job_complete', 'signature_capture'                             => 'manual',
            default                                                         => 'server_wins',
        };
    }
}
