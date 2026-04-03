<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Store;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store = null;

        // 1. Resolve by Authenticated User (Admin/Owner dashboard context)
        if ($request->hasSession() && auth()->check()) {
            $user = auth()->user();

            // Super admin does not need tenant binding
            if ($user->isSuperAdmin()) {
                return $next($request);
            }

            if ($user->isStoreOwner()) {
                $store = $user->ownedStores()->first();
            } elseif ($user->isStaff() && $user->store_id) {
                $store = $user->store;
            }
        }

        // 2. Resolve by Custom Domain / Host (Storefront context)
        if (!$store) {
            $host = $request->getHost();
            $store = Store::where('domain', $host)->where('status', 'active')->first();
        }

        // 3. Resolve by Slug (Storefront via /store/{store} route)
        if (!$store && $request->route('store')) {
            $storeParam = $request->route('store');
            if ($storeParam instanceof Store) {
                $store = $storeParam;
            } else {
                $store = Store::where('slug', $storeParam)
                    ->orWhere('id', $storeParam)
                    ->where('status', 'active')
                    ->first();
            }
        }

        // 4. Resolve by Header (API context)
        if (!$store && $request->hasHeader('X-Store-ID')) {
            $store = Store::find($request->header('X-Store-ID'));
        }

        if ($store) {
            app()->instance('current_tenant_id', $store->id);
        }

        return $next($request);
    }
}
