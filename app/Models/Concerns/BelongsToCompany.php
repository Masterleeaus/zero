<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Company;
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

    protected function initializeBelongsToCompany(): void
    {
        $this->fillable[] = 'company_id';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where($query->qualifyColumn('company_id'), $companyId);
    }
}
