<?php

namespace Modules\Documents\Services\Links;

use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentLink;

class DocumentLinkService
{
    public function addLink(Document $document, string $linkedType, int $linkedId, ?string $label = null): DocumentLink
    {
        return DocumentLink::firstOrCreate([
            'tenant_id' => $document->tenant_id,
            'document_id' => $document->id,
            'linked_type' => $linkedType,
            'linked_id' => $linkedId,
        ], [
            'label' => $label,
        ]);
    }
}
