<?php

namespace Modules\PropertyManagement\Entities\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = null;

            // Worksuite commonly exposes a company() helper in tenant context.
            if (function_exists('company')) {
                try {
                    $c = company();
                    if ($c) {
                        $companyId = $c->id ?? null;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            if (!$companyId && function_exists('user')) {
                try {
                    $u = user();
                    $companyId = $u->company_id ?? null;
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            if ($companyId) {
                $builder->where($builder->getModel()->getTable() . '.company_id', $companyId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $companyId = null;
                if (function_exists('company')) {
                    try {
                        $c = company();
                        $companyId = $c->id ?? null;
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                if (!$companyId && function_exists('user')) {
                    try {
                        $u = user();
                        $companyId = $u->company_id ?? null;
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                if ($companyId) {
                    $model->company_id = $companyId;
                }
            }
        });
    }
}
