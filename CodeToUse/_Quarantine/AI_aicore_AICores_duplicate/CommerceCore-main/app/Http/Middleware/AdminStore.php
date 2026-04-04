<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin can access everything — no tenant scope applied
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Store owner — resolve their first owned store
        if ($user->isStoreOwner()) {
            $store = $user->ownedStores()->first();
            if (!$store) {
                return redirect()->route('admin.stores.create')
                    ->with('warning', 'Please create a store to continue.');
            }

            // ✅ FIX: Bind the tenant ID into the IoC container to activate StoreScope
            app()->instance('current_tenant_id', $store->id);
            session(['admin_store_id' => $store->id]);
            $request->merge(['admin_store' => $store]);
            view()->share('adminStore', $store);

            return $next($request);
        }

        // Staff — use their assigned store
        if ($user->isStaff() && $user->store_id) {
            $store = $user->store;

            if (!$store || $store->status !== 'active') {
                abort(403, 'Your store is inactive or not found.');
            }

            // ✅ FIX: Bind the tenant ID into the IoC container to activate StoreScope
            app()->instance('current_tenant_id', $store->id);
            session(['admin_store_id' => $store->id]);
            $request->merge(['admin_store' => $store]);
            view()->share('adminStore', $store);

            return $next($request);
        }

        abort(403, 'Unauthorized access. No store assigned to your account.');
    }
}
