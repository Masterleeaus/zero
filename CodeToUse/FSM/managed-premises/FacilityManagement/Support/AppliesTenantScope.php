<?php

namespace Modules\FacilityManagement\Support;

use Illuminate\Support\Facades\Schema;

trait AppliesTenantScope
{
    protected static function bootAppliesTenantScope()
    {
        static::addGlobalScope('facilityTenant', function ($builder) {
            $tenantId = app()->bound('facility_tenant_id') ? app('facility_tenant_id') : null;
            if (!$tenantId) return;
            $model = $builder->getModel();
            $table = $model->getTable();
            // Only apply if table has tenant_id
            if (Schema::hasColumn($table, 'tenant_id')) {
                $builder->where($table.'.tenant_id', $tenantId);
            }
        });
    }
}
