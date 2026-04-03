<?php

namespace Modules\Documents\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDocumentTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Placeholder for stricter tenant scoping if you add route model binding overrides.
        return $next($request);
    }
}
