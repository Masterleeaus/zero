<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StoreScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope if there is an active store set in the application context.
        // For now, we fetch it from a service or context. We will set this in TenantMiddleware.
        if (app()->has('current_tenant_id')) {
            // Bypass for super_admin
            if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isSuperAdmin()) {
                return;
            }

            $storeId = app('current_tenant_id');
            if ($storeId) {
                $builder->where($model->getTable() . '.store_id', '=', $storeId);
            }
        }
    }
}
