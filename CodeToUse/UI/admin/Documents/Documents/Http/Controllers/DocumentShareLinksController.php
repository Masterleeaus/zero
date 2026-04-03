<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Support\TenantResolver;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentShareLink;
use Modules\Documents\Http\Requests\StoreShareLinkRequest;

class DocumentShareLinksController extends Controller
{
    public function store(StoreShareLinkRequest $request, Document $document)
    {
        abort_unless(auth()->user()?->can('documents.share') || auth()->user()?->can('manage_documents'), 403);

        $tenantId = function_exists('company') && company() ? company()->id : (auth()->user()->tenant_id ?? auth()->id());

        // Tenant isolation (best-effort)
        if (isset($document->tenant_id)) {
            abort_unless((int) $document->tenant_id === (int) $tenantId, 404);
        }

        $data = $request->validated();

        $link = DocumentShareLink::create([
            'tenant_id' => $tenantId,
            'document_id' => $document->id,
            'expires_at' => $data['expires_at'] ?? null,
            'note' => $data['note'] ?? null,
            'created_by' => auth()->id(),
            'max_views' => $request->input('max_views'),
            'views_count' => 0,
        ]);

        return back()->with('status', __('Share link created.'))->with('share_link_token', $link->token);
    }

    public function revoke(Document $document, DocumentShareLink $shareLink)
    {
        abort_unless(auth()->user()?->can('documents.share') || auth()->user()?->can('manage_documents'), 403);

        $tenantId = function_exists('company') && company() ? company()->id : (auth()->user()->tenant_id ?? auth()->id());

        if (isset($document->tenant_id)) {
            abort_unless((int) $document->tenant_id === (int) $tenantId, 404);
        }

        abort_unless((int) $shareLink->document_id === (int) $document->id, 404);

        $shareLink->revoked_at = now();
        $shareLink->revoked_by = auth()->id();
        $shareLink->save();

        return back()->with('status', __('Share link revoked.'));
    }

    // Backwards-compatible destroy (hard delete)
    public function destroy(Document $document, DocumentShareLink $shareLink)
    {
        return $this->revoke($document, $shareLink);
    }
}
