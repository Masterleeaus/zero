<?php

declare(strict_types=1);

namespace App\Extensions\TitanTrust\System\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait TenantScoped
{
    public static function bootTenantScoped(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Only apply when auth is available; keeps CLI/migrations safe.
            if (auth()->check()) {
                $userId = (int) auth()->id();
                $companyId = (int) (auth()->user()->company_id ?? $userId);
                $builder->where($builder->getModel()->getTable() . '.company_id', $companyId)
                        ->where($builder->getModel()->getTable() . '.user_id', $userId);
            }
        });
    }

    public function scopeTenant(Builder $query, int $companyId, int $userId): Builder
    {
        return $query->where($this->getTable() . '.company_id', $companyId)
                     ->where($this->getTable() . '.user_id', $userId);
    }
}
