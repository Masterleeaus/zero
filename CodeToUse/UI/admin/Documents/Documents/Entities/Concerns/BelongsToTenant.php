<?php

namespace Modules\Documents\Entities\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected string $tenantKey = 'tenant_id';

    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = documents_tenant_id();
            if ($tenantId === null) {
                return;
            }
            $model = $builder->getModel();
            $key = property_exists($model, 'tenantKey') ? $model->tenantKey : 'tenant_id';
            $builder->where($model->getTable() . '.' . $key, $tenantId);
        });

        static::creating(function ($model) {
            $tenantId = documents_tenant_id();
            if ($tenantId === null) {
                return;
            }
            $key = property_exists($model, 'tenantKey') ? $model->tenantKey : 'tenant_id';
            if (empty($model->{$key})) {
                $model->{$key} = $tenantId;
            }
        });
    }
}
