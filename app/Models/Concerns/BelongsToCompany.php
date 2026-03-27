<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::$companyIdResolved = false;
        static::$resolvedCompanyId = null;
        static::$resolvedUserId = null;

        static::addGlobalScope('company', static function (Builder $builder) {
            $companyId = static::resolveCompanyId();

            if ($companyId !== null) {
                $builder->where($builder->qualifyColumn('company_id'), $companyId);
            }
        });

        static::creating(static function ($model) {
            $companyId = static::resolveCompanyId();

            if (is_null($model->company_id) && $companyId !== null) {
                $model->company_id = $companyId;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query
            ->withoutGlobalScope('company')
            ->where($query->qualifyColumn('company_id'), $companyId);
    }

    protected static function resolveCompanyId(): ?int
    {
        $authId = Auth::id();

        if (static::$companyIdResolved && static::$resolvedUserId === $authId) {
            return static::$resolvedCompanyId;
        }

        static::$resolvedUserId = $authId;
        static::$resolvedCompanyId = Auth::user()?->company_id;
        static::$companyIdResolved = true;

        return static::$resolvedCompanyId;
    }

    protected static bool $companyIdResolved = false;

    protected static ?int $resolvedCompanyId = null;

    protected static ?int $resolvedUserId = null;
}
