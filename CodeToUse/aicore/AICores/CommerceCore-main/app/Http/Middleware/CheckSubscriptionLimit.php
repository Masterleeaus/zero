<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;
use App\Models\Store;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimit
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('check.limit:products')
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        if (!app()->has('current_tenant_id')) {
            return $next($request);
        }

        $store = Store::find(app('current_tenant_id'));
        if (!$store) {
            return $next($request);
        }

        $service = app(SubscriptionService::class);

        // Count the current usage for this resource
        $currentCount = match ($resource) {
            'products' => $store->products()->count(),
            'pages' => $store->pages()->count(),
            default => 0,
        };

        if (!$service->checkResourceLimit($store, $resource, $currentCount)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "You have reached the maximum limit for {$resource} on your current plan. Please upgrade."
                ], 403);
            }

            return back()->with('error', "You have reached the maximum limit for {$resource} on your current plan. Please upgrade.");
        }

        return $next($request);
    }
}
