<?php

namespace Modules\Documents\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Documents\Entities\DocumentShareLink;

class VerifyDocumentShareLink
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->route('token');

        $link = DocumentShareLink::query()
            ->where('token', $token)
            ->first();

        if (!$link) {
            abort(404);
        }

        if ($link->revoked_at) {
            abort(410, 'This share link has been revoked.');
        }

        if ($link->expires_at && now()->greaterThan($link->expires_at)) {
            abort(410, 'This share link has expired.');
        }

        if ($link->max_views !== null && $link->views_count >= $link->max_views) {
            abort(410, 'This share link has reached its maximum number of views.');
        }

        // Attach for downstream
        $request->attributes->set('document_share_link', $link);

        return $next($request);
    }
}
