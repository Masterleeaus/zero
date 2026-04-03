<?php

namespace Modules\Inventory\Observers;

trait TenantObserver
{
    public static function bootTenantObserver()
    {
        static::creating(function ($model) {
            if (property_exists($model,'tenant_id') || in_array('tenant_id',$model->getFillable())) {
                $tenantId = app()->bound('inventory.tenant_id') ? app()->make('inventory.tenant_id') : null;
                if ($tenantId && empty($model->tenant_id)) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }
}
