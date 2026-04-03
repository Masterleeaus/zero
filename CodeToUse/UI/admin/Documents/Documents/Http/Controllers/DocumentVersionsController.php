<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentVersion;

class DocumentVersionsController extends Controller
{
    public function index(Document $document)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $versions = $document->versions()->paginate(30);

        return view('documents::documents.versions.index', compact('document', 'versions'));
    }

    public function show(Document $document, DocumentVersion $version)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);
        abort_unless((int) $version->document_id === (int) $document->id, 404);

        return view('documents::documents.versions.show', compact('document', 'version'));
    }
}
