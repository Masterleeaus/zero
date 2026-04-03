<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentLink;
use Modules\Documents\Http\Requests\StoreDocumentLinkRequest;

class DocumentLinksController extends Controller
{
    public function store(StoreDocumentLinkRequest $request, Document $document)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $data = $request->validated();

        DocumentLink::create([
            'tenant_id' => $tenantId,
            'document_id' => $document->id,
            'linked_type' => $data['linked_type'],
            'linked_id' => $data['linked_id'],
            'label' => $data['label'] ?? null,
        ]);

        return back()->with('status', __('Link added (scaffold).'));
    }

    public function destroy(Document $document, DocumentLink $link)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);
        abort_unless((int) $link->document_id === (int) $document->id, 404);

        $link->delete();

        return back()->with('status', __('Link removed.'));
    }
}
