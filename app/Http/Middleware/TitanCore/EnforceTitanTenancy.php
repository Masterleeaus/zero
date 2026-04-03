<?php

namespace App\Http\Middleware\TitanCore;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforceTitanTenancy — rejects requests where company_id cannot be resolved.
 */
class EnforceTitanTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        $user      = $request->user();
        $companyId = $user?->company_id ?? null;

        if (empty($companyId)) {
            return response()->json(['ok' => false, 'error' => 'Tenancy required: company_id missing'], 403);
        }

        return $next($request);
    }
}
