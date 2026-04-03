<?php

namespace Modules\Documents\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDocumentsPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (auth()->check() && auth()->user()->can($permission)) {
            return $next($request);
        }

        abort(403);
    }
}
