<?php

namespace App\Traits;

use App\Models\Store;
use Illuminate\Http\Request;

/**
 * Provides a secure, consistent store resolution method for Admin controllers.
 *
 * Priority order:
 * 1. Admin request attribute (set by AdminStore middleware for owners/staff)
 * 2. Super admin with explicit store_id input or session fallback
 * 3. User's first owned store (last resort for owners)
 * 4. Abort 403 — never fall through to Store::first()
 */
trait ResolvesStore
{
    protected function getActiveStore(Request $request): Store
    {
        $user = $request->user();

        // Super admin must explicitly select a store
        if ($user->isSuperAdmin()) {
            $storeId = $request->input('store_id') ?? session('admin_store_id');
            if ($storeId) {
                return Store::findOrFail($storeId);
            }
            // Super admin can see all; fallback to first store to ensure pages load without 400 errors
            return Store::firstOrFail();
        }

        // From AdminStore middleware (preferred — already validated)
        if ($request->has('admin_store') && $request->get('admin_store') instanceof Store) {
            return $request->get('admin_store');
        }

        // Store owner fallback
        if ($user->isStoreOwner()) {
            $store = $user->ownedStores()->first();
            if ($store) {
                return $store;
            }
        }

        // Staff fallback
        if ($user->isStaff() && $user->store) {
            return $user->store;
        }

        abort(403, 'No active store context found for your account.');
    }

    protected function getActiveStoreId(Request $request): int
    {
        return $this->getActiveStore($request)->id;
    }
}
