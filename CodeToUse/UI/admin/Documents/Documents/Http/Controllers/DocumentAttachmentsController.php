<?php

namespace Modules\Documents\Http\Controllers;

use Modules\Documents\Support\TenantResolver;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentFile;

class DocumentAttachmentsController extends Controller
{
    public function destroy(Document $document, DocumentFile $attachment)
    {
        $tenantId = TenantResolver::id();

        abort_if($document->tenant_id !== $tenantId, 403);
        abort_if($attachment->tenant_id !== $tenantId, 403);
        abort_if($attachment->document_id !== $document->id, 403);

        if (Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();

        return back()->with('success', __('Attachment removed.'));
    }
}
