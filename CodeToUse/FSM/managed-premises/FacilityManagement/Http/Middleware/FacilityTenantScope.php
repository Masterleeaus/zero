<?php

namespace Modules\FacilityManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FacilityTenantScope
{
    public function handle(Request $request, Closure $next)
    {
        // Determine tenant id from header, session, or config
        $tenantId = $request->header('X-Tenant-ID') ?? config('app.tenant_id');
        if ($tenantId) {
            // Put into container for later
            app()->instance('facility_tenant_id', $tenantId);
        }
        return $next($request);
    }
}
