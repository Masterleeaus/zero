<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentVersion;
use Modules\Documents\Services\Versioning\DocumentVersionService;

class DocumentRestoreVersionController extends Controller
{
    public function __construct(protected DocumentVersionService $versions)
    {
    }

    public function restore(Document $document, DocumentVersion $version)
    {
        $this->authorize('restore', $document);
        abort_unless((int) $version->document_id === (int) $document->id, 404);

        $this->versions->restore($document, $version);

        return redirect()->route('documents.show', $document)->with('status', __('Version restored.'));
    }
}
