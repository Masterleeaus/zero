<?php

namespace Modules\Documents\Services\Versioning;

use Illuminate\Support\Facades\DB;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentVersion;

class DocumentVersionService
{
    public function restore(Document $document, DocumentVersion $version): void
    {
        $snapshot = $version->snapshot ?? [];

        DB::transaction(function () use ($document, $snapshot) {
            if (isset($snapshot['document'])) {
                // Restore core fields safely
                $core = $snapshot['document'];
                $allow = ['title','type','category','subcategory','template_slug','body_markdown','status','effective_at','review_at','trade','role'];
                foreach ($allow as $k) {
                    if (array_key_exists($k, $core)) {
                        $document->{$k} = $core[$k];
                    }
                }
                $document->save();
            }

            // Restore sections
            $document->sections()->delete();
            foreach (($snapshot['sections'] ?? []) as $section) {
                unset($section['id']);
                $section['tenant_id'] = $document->tenant_id;
                $section['document_id'] = $document->id;
                \Modules\Documents\Entities\DocumentSection::create($section);
            }

            // Restore metadata
            $document->metadata()->delete();
            foreach (($snapshot['metadata'] ?? []) as $meta) {
                unset($meta['id']);
                $meta['tenant_id'] = $document->tenant_id;
                $meta['document_id'] = $document->id;
                \Modules\Documents\Entities\DocumentMetadata::create($meta);
            }
        });
    }
}
