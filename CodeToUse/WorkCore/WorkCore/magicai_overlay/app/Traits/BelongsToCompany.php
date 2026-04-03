<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    public function scopeForCompany(Builder $query, ?int $companyId = null): Builder
    {
        $companyId ??= tenant();

        if (! $companyId) {
            return $query;
        }

        return $query->where($this->getTable() . '.company_id', $companyId);
    }
}
