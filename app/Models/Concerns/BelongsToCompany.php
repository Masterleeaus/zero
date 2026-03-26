<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::creating(static function ($model) {
            if (empty($model->company_id) && auth()->check()) {
                $model->company_id = auth()->user()?->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, $companyId): Builder
    {
        return $query->where($query->qualifyColumn('company_id'), $companyId);
    }
}
