<?php

namespace Modules\FacilityManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FacilityReportsAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->can('facilities.view')) {
            abort(403, 'You do not have permission to view facility reports.');
        }
        return $next($request);
    }
}
