<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentShareLink;
use Modules\Documents\Http\Requests\StoreShareLinkRequest;

class DocumentShareLinksController extends Controller
{
    public function store(StoreShareLinkRequest $request, Document $document)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $data = $request->validated();

        DocumentShareLink::create([
            'tenant_id' => $tenantId,
            'document_id' => $document->id,
            'expires_at' => $data['expires_at'] ?? null,
            'note' => $data['note'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('status', __('Share link created (scaffold).'));
    }

    public function destroy(Document $document, DocumentShareLink $shareLink)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);
        abort_unless((int) $shareLink->document_id === (int) $document->id, 404);

        $shareLink->delete();

        return back()->with('status', __('Share link removed.'));
    }
}
