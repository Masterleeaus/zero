<?php

declare(strict_types=1);

namespace App\Services\Docs;

use App\Events\Docs\DocumentVersionPublished;
use App\Models\Premises\FacilityDocument;
use Illuminate\Support\Facades\DB;

/**
 * DocumentVersionService
 *
 * Manages document versioning via the supersedes_id self-referential link.
 * A new version is a new FacilityDocument record pointing at the old one
 * via supersedes_id; the old record is marked as 'superseded'.
 */
class DocumentVersionService
{
    /**
     * Publish a new version of an existing document.
     *
     * Creates a new FacilityDocument record that supersedes the given one,
     * marks the old document as 'superseded', and fires the versioning event.
     *
     * @param  array<string, mixed>  $data  Attributes for the new version record
     */
    public function publishNewVersion(FacilityDocument $document, array $data): FacilityDocument
    {
        return DB::transaction(function () use ($document, $data) {
            // Resolve the root of the version chain to keep chains flat
            $root = $this->getRootDocument($document);

            $newDoc = FacilityDocument::create(array_merge(
                // Inherit base attributes from the current document
                [
                    'company_id'              => $document->company_id,
                    'documentable_type'       => $document->documentable_type,
                    'documentable_id'         => $document->documentable_id,
                    'doc_type'                => $document->doc_type,
                    'document_category'       => $document->document_category,
                    'title'                   => $document->title,
                    'applies_to_asset_types'  => $document->applies_to_asset_types,
                    'applies_to_job_types'    => $document->applies_to_job_types,
                    'applies_to_service_types' => $document->applies_to_service_types,
                    'access_level_minimum'    => $document->access_level_minimum,
                    'requires_certification'  => $document->requires_certification,
                    'is_mandatory'            => $document->is_mandatory,
                    'status'                  => 'valid',
                    'uploaded_by'             => $document->uploaded_by,
                ],
                // Override with caller-supplied data (file path, version string, review date …)
                $data,
                // Always link to the current (latest) document
                ['supersedes_id' => $document->id],
            ));

            // Mark the superseded document
            $document->update(['status' => 'superseded']);

            DocumentVersionPublished::dispatch($newDoc, $document);

            return $newDoc;
        });
    }

    /**
     * Resolve the active (latest, non-superseded) version for an original document ID.
     *
     * Follows the supersedes_id chain upward until a document with no newer
     * version is found.
     */
    public function getActiveVersion(int $originalDocId): FacilityDocument
    {
        $doc = FacilityDocument::findOrFail($originalDocId);

        // Walk forward: find the document that supersedes this one
        while (true) {
            $newer = FacilityDocument::query()
                ->where('supersedes_id', $doc->id)
                ->where('status', '!=', 'superseded')
                ->orderByDesc('id')
                ->first();

            if ($newer === null) {
                break;
            }

            $doc = $newer;
        }

        return $doc;
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    /** Walk backward through supersedes_id links to find the root document. */
    private function getRootDocument(FacilityDocument $document): FacilityDocument
    {
        $current = $document;

        while ($current->supersedes_id !== null) {
            $parent = FacilityDocument::find($current->supersedes_id);

            if ($parent === null) {
                break;
            }

            $current = $parent;
        }

        return $current;
    }
}
