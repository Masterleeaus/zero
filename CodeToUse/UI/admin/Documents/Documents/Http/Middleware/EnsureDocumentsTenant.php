<?php

namespace Modules\Documents\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDocumentsTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Tenant context must exist for all account routes
        if (! documents_tenant_id()) {
            abort(403, 'Tenant context missing');
        }

        return $next($request);
    }
}
