<?php

namespace Modules\TitanHello\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next)
    {
        // Placeholder for tenant scoping checks (company_id/account_id etc).
        // Later passes will enforce tenant scoping across repositories & policies.
        return $next($request);
    }
}
