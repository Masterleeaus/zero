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
        $request = app()->bound('request') ? request() : null;

        if ($request?->attributes->has('company_scope_company_id')) {
            $cachedUserId = $request->attributes->get('company_scope_user_id');

            if ($cachedUserId === Auth::id()) {
                return $request->attributes->get('company_scope_company_id');
            }
        }

        $companyId = Auth::user()?->company_id;

        if ($request) {
            $request->attributes->set('company_scope_company_id', $companyId);
            $request->attributes->set('company_scope_user_id', Auth::id());
        }

        return $companyId;
    }
}
