<?php

namespace Modules\Documents\Services\Pdf;

use Modules\Documents\Entities\Document;

class DocumentPdfRenderer
{
    public function viewFor(Document $document): string
    {
        return $document->type === 'swms' ? 'documents::pdf.swms' : 'documents::pdf.document';
    }
}
