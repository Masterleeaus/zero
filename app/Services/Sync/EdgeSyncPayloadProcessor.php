<?php

declare(strict_types=1);

namespace App\Services\Sync;

use App\Models\Inspection\InspectionInstance;
use App\Models\Inspection\InspectionResponse;
use App\Models\Sync\EdgeSyncQueue;
use App\Models\Work\ChecklistResponse;
use App\Models\Work\ChecklistRun;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MODULE 05 — TitanEdgeSync
 *
 * Applies the concrete database mutations for each operation type.
 * Called by EdgeSyncService::processOperation() after conflict detection.
 */
class EdgeSyncPayloadProcessor
{
    /**
     * Apply a job field update from an offline payload.
     */
    public function applyJobUpdate(EdgeSyncQueue $item): void
    {
        $payload = $item->payload;
        $job     = ServiceJob::findOrFail((int) $item->subject_id);

        $allowedFields = [
            'status', 'notes', 'outcome', 'outcome_notes',
            'date_start', 'date_end', 'completed_at',
        ];

        $updates = array_intersect_key($payload, array_flip($allowedFields));

        if (! empty($updates)) {
            $job->update($updates);
        }

        Log::debug('edge_sync.job_update_applied', [
            'queue_id' => $item->id,
            'job_id'   => $job->id,
            'fields'   => array_keys($updates),
        ]);
    }

    /**
     * Apply a checklist response submitted offline.
     */
    public function applyChecklistResponse(EdgeSyncQueue $item): void
    {
        $payload = $item->payload;

        $run = ChecklistRun::findOrFail((int) ($payload['checklist_run_id'] ?? $item->subject_id));

        $responseData = [
            'checklist_run_id'  => $run->id,
            'checklist_item_id' => $payload['checklist_item_id'],
            'result'            => $payload['result'] ?? null,
            'is_checked'        => $payload['is_checked'] ?? false,
            'numeric_value'     => $payload['numeric_value'] ?? null,
            'text_value'        => $payload['text_value'] ?? null,
            'notes'             => $payload['notes'] ?? null,
            'photo_path'        => $payload['photo_path'] ?? null,
            'signature_captured' => $payload['signature_captured'] ?? false,
            'responded_by'      => $item->user_id,
            'responded_at'      => $item->client_created_at ?? now(),
        ];

        ChecklistResponse::updateOrCreate(
            [
                'checklist_run_id'  => $run->id,
                'checklist_item_id' => $payload['checklist_item_id'],
            ],
            $responseData
        );

        // Refresh run completion counts.
        $completed = ChecklistResponse::where('checklist_run_id', $run->id)
            ->whereNotNull('result')
            ->count();
        $failed = ChecklistResponse::where('checklist_run_id', $run->id)
            ->where('result', 'fail')
            ->count();

        $run->update([
            'items_completed' => $completed,
            'items_failed'    => $failed,
        ]);

        Log::debug('edge_sync.checklist_response_applied', [
            'queue_id' => $item->id,
            'run_id'   => $run->id,
        ]);
    }

    /**
     * Apply an inspection response submitted offline.
     */
    public function applyInspectionResponse(EdgeSyncQueue $item): void
    {
        $payload    = $item->payload;
        $instanceId = (int) ($payload['inspection_instance_id'] ?? $item->subject_id);

        InspectionInstance::findOrFail($instanceId);

        // InspectionResponse may not exist in all deployments — guard gracefully.
        if (! class_exists(InspectionResponse::class)) {
            Log::warning('edge_sync.inspection_response_class_missing', ['queue_id' => $item->id]);

            return;
        }

        InspectionResponse::updateOrCreate(
            [
                'inspection_instance_id' => $instanceId,
                'inspection_item_id'     => $payload['inspection_item_id'],
            ],
            [
                'inspection_instance_id' => $instanceId,
                'inspection_item_id'     => $payload['inspection_item_id'],
                'result'                 => $payload['result'] ?? null,
                'notes'                  => $payload['notes'] ?? null,
                'photo_path'             => $payload['photo_path'] ?? null,
                'responded_by'           => $item->user_id,
                'responded_at'           => $item->client_created_at ?? now(),
            ]
        );

        Log::debug('edge_sync.inspection_response_applied', [
            'queue_id'    => $item->id,
            'instance_id' => $instanceId,
        ]);
    }

    /**
     * Apply a signature capture recorded offline.
     */
    public function applySignatureCapture(EdgeSyncQueue $item): void
    {
        $payload = $item->payload;
        $job     = ServiceJob::findOrFail((int) $item->subject_id);

        $updates = ['require_signature' => false];

        if (! empty($payload['signature_path'])) {
            $updates['signature_path'] = $payload['signature_path'];
        }
        if (! empty($payload['signed_by'])) {
            $updates['signed_by'] = $payload['signed_by'];
        }
        if (! empty($payload['signed_at'])) {
            $updates['signed_at'] = $payload['signed_at'];
        }

        $job->update($updates);

        Log::debug('edge_sync.signature_capture_applied', [
            'queue_id' => $item->id,
            'job_id'   => $job->id,
        ]);
    }

    /**
     * Apply a full job completion recorded offline.
     */
    public function applyJobCompletion(EdgeSyncQueue $item): void
    {
        $payload = $item->payload;
        $job     = ServiceJob::findOrFail((int) $item->subject_id);

        DB::transaction(function () use ($job, $payload, $item) {
            $updates = [
                'status'        => 'completed',
                'completed_at'  => $payload['completed_at'] ?? $item->client_created_at ?? now(),
            ];

            if (! empty($payload['outcome'])) {
                $updates['outcome'] = $payload['outcome'];
            }
            if (! empty($payload['outcome_notes'])) {
                $updates['outcome_notes'] = $payload['outcome_notes'];
            }

            $job->update($updates);
        });

        Log::debug('edge_sync.job_completion_applied', [
            'queue_id' => $item->id,
            'job_id'   => $job->id,
        ]);
    }
}
