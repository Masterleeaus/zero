<?php

declare(strict_types=1);

namespace App\Services\Sync;

use App\Events\Sync\EdgeBatchSynced;
use App\Events\Sync\EdgeConflictDetected;
use App\Events\Sync\EdgeSyncFailed;
use App\Models\Sync\EdgeDeviceSession;
use App\Models\Sync\EdgeSyncConflict;
use App\Models\Sync\EdgeSyncLog;
use App\Models\Sync\EdgeSyncQueue;
use App\Models\User;
use App\Titan\Signals\SignalDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * MODULE 05 — TitanEdgeSync
 *
 * Orchestrates the full offline sync lifecycle:
 *  1. Device registration
 *  2. Batch ingestion (push from device)
 *  3. Per-operation processing with conflict detection
 *  4. Delta fetch (pull to device)
 *  5. Batch acknowledgement
 */
class EdgeSyncService
{
    public function __construct(
        private readonly EdgeSyncPayloadProcessor  $processor,
        private readonly EdgeConflictResolverService $resolver,
        private readonly SignalDispatcher            $signals,
    ) {}

    // ── Device registration ───────────────────────────────────────────────────

    /**
     * Register or refresh a device session.
     *
     * @param  array{device_id: string, device_name?: string, platform?: string} $deviceData
     */
    public function registerDevice(User $user, array $deviceData): EdgeDeviceSession
    {
        /** @var EdgeDeviceSession $session */
        $session = EdgeDeviceSession::withoutGlobalScopes()->updateOrCreate(
            [
                'user_id'   => $user->id,
                'device_id' => $deviceData['device_id'],
            ],
            [
                'company_id'  => $user->company_id,
                'device_name' => $deviceData['device_name'] ?? null,
                'platform'    => $deviceData['platform']    ?? 'pwa',
                'is_active'   => true,
                'last_sync_at' => now(),
            ]
        );

        return $session;
    }

    // ── Batch ingestion ───────────────────────────────────────────────────────

    /**
     * Ingest a batch of operations pushed from a device.
     *
     * Operations are inserted into the queue and processed in
     * client_created_at order per the specification.
     *
     * @param  array<int, array<string, mixed>> $operations
     * @return array{batch_id: string, accepted: int, conflicts: int, failed: int}
     */
    public function ingestBatch(User $user, string $deviceId, array $operations): array
    {
        $batchId = (string) Str::uuid();

        $this->signals->dispatch('sync.batch_received', [
            'user_id'          => $user->id,
            'device_id'        => $deviceId,
            'batch_id'         => $batchId,
            'operations_count' => count($operations),
        ]);

        /** @var EdgeSyncLog $syncLog */
        $syncLog = EdgeSyncLog::create([
            'company_id'       => $user->company_id,
            'user_id'          => $user->id,
            'device_id'        => $deviceId,
            'batch_id'         => $batchId,
            'operations_count' => count($operations),
            'started_at'       => now(),
        ]);

        // Persist all operations first, then process in chronological order.
        $queueItems = [];

        foreach ($operations as $op) {
            $item = EdgeSyncQueue::create([
                'company_id'        => $user->company_id,
                'device_id'         => $deviceId,
                'user_id'           => $user->id,
                'operation_type'    => $op['operation_type'],
                'subject_type'      => $op['subject_type']      ?? null,
                'subject_id'        => $op['subject_id']        ?? null,
                'payload'           => $op['payload']           ?? [],
                'client_created_at' => $op['client_created_at'] ?? null,
                'status'            => 'pending',
            ]);

            $queueItems[] = $item;
        }

        // Sort by client_created_at ascending (null-safe: nulls last).
        usort($queueItems, static function (EdgeSyncQueue $a, EdgeSyncQueue $b): int {
            if ($a->client_created_at === null && $b->client_created_at === null) {
                return 0;
            }
            if ($a->client_created_at === null) {
                return 1;
            }
            if ($b->client_created_at === null) {
                return -1;
            }

            return $a->client_created_at <=> $b->client_created_at;
        });

        $conflictsCount = 0;
        $failedCount    = 0;

        foreach ($queueItems as $item) {
            $success = $this->processOperation($item);

            if ($item->isConflict()) {
                $conflictsCount++;
            } elseif ($item->isFailed()) {
                $failedCount++;
            }
        }

        $syncLog->complete($conflictsCount, $failedCount);

        // Advance device cursor to the latest processed EdgeSyncQueue ID.
        $lastId = collect($queueItems)->max('id');
        $this->advanceDeviceCursor($user, $deviceId, (int) $lastId);

        event(new EdgeBatchSynced($syncLog));

        $this->signals->dispatch('sync.batch_complete', [
            'batch_id'         => $batchId,
            'operations_count' => count($operations),
            'conflicts_count'  => $conflictsCount,
            'failed_count'     => $failedCount,
        ]);

        return [
            'batch_id'  => $batchId,
            'accepted'  => count($operations) - $failedCount,
            'conflicts' => $conflictsCount,
            'failed'    => $failedCount,
        ];
    }

    // ── Operation processing ──────────────────────────────────────────────────

    /**
     * Process a single queued sync operation.
     * Returns true on success (synced), false on conflict or failure.
     */
    public function processOperation(EdgeSyncQueue $item): bool
    {
        $item->markProcessing();

        try {
            $conflict = $this->detectConflicts($item);

            if ($conflict !== null) {
                $item->markConflict();

                event(new EdgeConflictDetected($item, $conflict));

                $this->signals->dispatch('sync.conflict_detected', [
                    'queue_id'      => $item->id,
                    'conflict_type' => $conflict->conflict_type,
                ]);

                // Attempt auto-resolution.
                $resolved = $this->resolver->autoResolve($conflict);

                if (! $resolved) {
                    return false;
                }
            } else {
                $this->applyOperation($item);
                $item->markSynced();
            }

            return true;
        } catch (\Throwable $e) {
            $item->markFailed($e->getMessage());

            event(new EdgeSyncFailed($item, $e->getMessage()));

            Log::error('edge_sync.process_operation_failed', [
                'queue_id' => $item->id,
                'error'    => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Detect whether the incoming operation conflicts with server state.
     */
    public function detectConflicts(EdgeSyncQueue $item): ?EdgeSyncConflict
    {
        if ($item->subject_type === null || $item->subject_id === null) {
            return null;
        }

        // Resolve the server-side model class.
        $modelClass = $this->resolveModelClass($item->subject_type);

        if ($modelClass === null) {
            return null;
        }

        /** @var \Illuminate\Database\Eloquent\Model|null $serverRecord */
        $serverRecord = $modelClass::find($item->subject_id);

        if ($serverRecord === null) {
            // Subject deleted on server — deleted_subject conflict.
            return EdgeSyncConflict::create([
                'sync_queue_id' => $item->id,
                'conflict_type' => 'deleted_subject',
                'server_state'  => null,
                'client_state'  => $item->payload,
            ]);
        }

        // Compare client_created_at against the server record's updated_at.
        if (
            $item->client_created_at !== null
            && $serverRecord->updated_at !== null
            && $serverRecord->updated_at > $item->client_created_at
        ) {
            return EdgeSyncConflict::create([
                'sync_queue_id' => $item->id,
                'conflict_type' => 'version_mismatch',
                'server_state'  => $serverRecord->toArray(),
                'client_state'  => $item->payload,
            ]);
        }

        return null;
    }

    /**
     * Resolve a conflict using the given strategy.
     *
     * Strategies: server_wins|client_wins|merge|manual
     */
    public function resolveConflict(EdgeSyncConflict $conflict, string $strategy): void
    {
        $this->resolver->applyResolution($conflict, $strategy);

        $this->signals->dispatch('sync.conflict_resolved', [
            'conflict_id' => $conflict->id,
            'strategy'    => $strategy,
        ]);
    }

    // ── Delta / pull ──────────────────────────────────────────────────────────

    /**
     * Return server-side changes since the device's last sync cursor.
     *
     * The cursor is the last edge_sync_queue.id processed by the device.
     * Returns recently modified jobs, checklist runs, and inspection instances
     * that belong to the same company, scoped by the cursor.
     *
     * @return array<string, mixed>
     */
    public function getDeltaForDevice(EdgeDeviceSession $session): array
    {
        $cursor    = $session->sync_cursor;
        $companyId = $session->company_id;

        $jobs = \App\Models\Work\ServiceJob::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', '>', $cursor)
            ->orderBy('id')
            ->limit(200)
            ->get(['id', 'title', 'status', 'updated_at'])
            ->toArray();

        $checklistRuns = \App\Models\Work\ChecklistRun::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', '>', $cursor)
            ->orderBy('id')
            ->limit(200)
            ->get(['id', 'title', 'status', 'updated_at'])
            ->toArray();

        $inspectionInstances = \App\Models\Inspection\InspectionInstance::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', '>', $cursor)
            ->orderBy('id')
            ->limit(200)
            ->get(['id', 'title', 'status', 'updated_at'])
            ->toArray();

        return [
            'cursor'               => $cursor,
            'jobs'                 => $jobs,
            'checklist_runs'       => $checklistRuns,
            'inspection_instances' => $inspectionInstances,
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function applyOperation(EdgeSyncQueue $item): void
    {
        match ($item->operation_type) {
            'job_update'           => $this->processor->applyJobUpdate($item),
            'checklist_response'   => $this->processor->applyChecklistResponse($item),
            'inspection_response'  => $this->processor->applyInspectionResponse($item),
            'signature_capture'    => $this->processor->applySignatureCapture($item),
            'job_complete'         => $this->processor->applyJobCompletion($item),
            'evidence_upload'      => null, // handled by storage pipeline
            default                => Log::warning('edge_sync.unknown_operation_type', [
                'type'     => $item->operation_type,
                'queue_id' => $item->id,
            ]),
        };
    }

    private function resolveModelClass(string $subjectType): ?string
    {
        $map = [
            'service_job'          => \App\Models\Work\ServiceJob::class,
            'checklist_run'        => \App\Models\Work\ChecklistRun::class,
            'inspection_instance'  => \App\Models\Inspection\InspectionInstance::class,
        ];

        // Also accept fully-qualified class names.
        if (class_exists($subjectType)) {
            return $subjectType;
        }

        return $map[$subjectType] ?? null;
    }

    private function advanceDeviceCursor(User $user, string $deviceId, int $cursor): void
    {
        $session = EdgeDeviceSession::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->first();

        $session?->advanceCursor($cursor);
    }
}
