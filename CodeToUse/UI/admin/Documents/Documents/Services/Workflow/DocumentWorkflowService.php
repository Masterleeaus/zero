<?php

namespace Modules\Documents\Services\Workflow;

use Illuminate\Support\Facades\DB;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentStatusHistory;
use Modules\Documents\Services\Versioning\DocumentSnapshotService;

class DocumentWorkflowService
{
    public function __construct(protected DocumentSnapshotService $snapshotService)
    {
    }

    /**
     * Change status safely and record history.
     * Titan Zero can propose changes, but Documents enforces perms elsewhere (policy/controller).
     */
    public function changeStatus(Document $document, string $toStatus, ?int $userId = null, ?string $note = null): void
    {
        DB::transaction(function () use ($document, $toStatus, $userId, $note) {
            $from = $document->status;

            $document->status = $toStatus;
            $document->status_changed_at = now();

            if ($toStatus === 'approved') {
                $document->approved_by = $userId;
                $document->approved_at = now();
            }

            if ($toStatus === 'archived') {
                $document->archived_at = now();
            }

            $document->save();

            DocumentStatusHistory::create([
                'tenant_id' => $document->tenant_id,
                'document_id' => $document->id,
                'from_status' => $from,
                'to_status' => $toStatus,
                'changed_by' => $userId,
                'note' => $note,
                'changed_at' => now(),
            ]);

            // Snapshot on meaningful transitions
            $this->snapshotService->snapshot($document, $userId, "status:$from->$toStatus");
        });
    }
}
