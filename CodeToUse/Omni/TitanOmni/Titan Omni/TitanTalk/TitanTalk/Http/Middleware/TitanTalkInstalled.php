<?php

namespace Modules\TitanTalk\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TitanTalkInstalled
{
    public function handle(Request $request, Closure $next)
    {
        // Soft-guard: only block if a specific module install marker exists and is false.
        // This avoids hard failures on environments without the expected tables.
        try {
            if (function_exists('module_enabled') && !module_enabled('TitanTalk')) {
                abort(404);
            }
        } catch (\Throwable $e) {
            // If helper/table not present, allow request; module will remain harmless.
        }

        return $next($request);
    }
}
