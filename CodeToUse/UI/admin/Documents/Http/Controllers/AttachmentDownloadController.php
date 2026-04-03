<?php

namespace Modules\Documents\Http\Controllers;

use Modules\Documents\Support\TenantResolver;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Entities\DocumentFile;

class AttachmentDownloadController extends Controller
{
    public function download(DocumentFile $attachment)
    {
        $tenantId = TenantResolver::id();

        abort_if($attachment->tenant_id !== $tenantId, 403);

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404);
        }

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }
}
