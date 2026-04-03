<?php

namespace Modules\Inventory\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WithTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Simple tenant propagation: from header X-Tenant-ID or authenticated user
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        if (!$tenantId && $request->user() && method_exists($request->user(),'tenant_id')) {
            $tenantId = (int) $request->user()->tenant_id;
        }
        if ($tenantId) {
            app()->instance('inventory.tenant_id', $tenantId);
        }
        return $next($request);
    }
}
