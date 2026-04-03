<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Support\TenantResolver;
use Modules\Documents\Entities\DocumentShareLink;
use Modules\Documents\Entities\DocumentShareLinkHit;
use Modules\Documents\Entities\Document;

class DocumentSharePublicController extends Controller
{
    public function show(Request $request, $token)
    {
        /** @var DocumentShareLink $link */
        $link = $request->attributes->get('document_share_link');

        $doc = Document::query()->findOrFail($link->document_id);

        // Count view + log hit (do not fail the page if logging fails)
        try {
            $link->increment('views_count');

            DocumentShareLinkHit::query()->create([
                'share_link_id' => $link->id,
                'document_id' => $doc->id,
                'tenant_id' => $link->tenant_id ?? null,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'viewed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        return view('documents::share.public_show', [
            'document' => $doc,
            'link' => $link,
        ]);
    }
}
