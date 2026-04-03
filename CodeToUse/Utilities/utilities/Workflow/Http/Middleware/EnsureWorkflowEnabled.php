<?php

namespace Modules\Workflow\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureWorkflowEnabled
{
    public function handle(Request $request, Closure $next)
    {
        // If your platform uses user_modules() gating, enforce it here for account routes.
        // Superadmin routes should not depend on tenant module enablement.
        if (function_exists('user_modules') && $request->is('account/*')) {
            $mods = user_modules() ?? [];
            if (!in_array('workflow', $mods) && !in_array('workflows', $mods)) {
                abort(404);
            }
        }

        return $next($request);
    }
}
