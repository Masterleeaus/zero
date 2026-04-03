<?php

namespace Modules\Documents\Services\Versioning;

use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentVersion;

class DocumentSnapshotService
{
    /**
     * Create a point-in-time snapshot. Safe to call even if versions table isn't ready yet.
     */
    public function snapshot(Document $document, ?int $userId = null, ?string $reason = null): void
    {
        if (!class_exists(DocumentVersion::class) || !\Illuminate\Support\Facades\Schema::hasTable('document_versions')) {
            return;
        }

        $nextNo = (int) (DocumentVersion::where('document_id', $document->id)->max('version_no') ?? 0) + 1;

        $snapshot = [
            'document' => $document->toArray(),
            'sections' => $document->sections()->get()->toArray(),
            'metadata' => $document->metadata()->get()->toArray(),
            'files'    => $document->files()->get()->toArray(),
        ];

        $hash = hash('sha256', json_encode($snapshot) ?: '');

        DocumentVersion::create([
            'tenant_id' => $document->tenant_id,
            'document_id' => $document->id,
            'version_no' => $nextNo,
            'reason' => $reason,
            'version_hash' => $hash,
            'snapshot' => $snapshot,
            'created_by' => $userId,
        ]);
    }
}
