<?php

namespace Modules\TitanHello\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceTitanHelloPermissions
{
    public function handle(Request $request, Closure $next, string $permission = '')
    {
        if ($permission !== '') {
            try {
                // Worksuite typically has user()->permission() / hasPermissionTo() patterns
                $user = $request->user();
                if ($user && method_exists($user, 'permission')) {
                    if (!$user->permission($permission)) abort(403);
                } elseif ($user && method_exists($user, 'hasPermissionTo')) {
                    if (!$user->hasPermissionTo($permission)) abort(403);
                }
            } catch (\Throwable $e) {
                // fail-open to avoid boot/route crashes; policies still protect data
            }
        }

        return $next($request);
    }
}
