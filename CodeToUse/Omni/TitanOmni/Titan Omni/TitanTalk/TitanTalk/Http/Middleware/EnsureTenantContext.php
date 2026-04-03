<?php

namespace Modules\TitanTalk\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next)
    {
        // Best-effort tenant resolution (WorkSuite varies by build)
        $tenantId = null;

        try {
            $user = auth()->user();
            if ($user) {
                foreach (['company_id','tenant_id','business_id'] as $k) {
                    if (isset($user->{$k}) && $user->{$k}) {
                        $tenantId = $user->{$k};
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (!$tenantId && function_exists('company')) {
                $c = company();
                if ($c && isset($c->id)) $tenantId = $c->id;
            }
        } catch (\Throwable $e) {}

        try {
            if (!$tenantId && session()->has('company_id')) {
                $tenantId = session('company_id');
            }
        } catch (\Throwable $e) {}

        if ($tenantId) {
            $request->attributes->set('titantalk_tenant_id', $tenantId);
        }

        return $next($request);
    }
}
