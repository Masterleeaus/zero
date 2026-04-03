<?php

namespace Modules\ManagedPremises\Entities\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Tenant scoping for WorkSuite (MVP rule): every table has BOTH company_id and user_id.
 *
 * Authoritative tenant isolation:
 * - Filter reads by company_id + user_id when available.
 * - On create, auto-fill company_id + user_id from current tenant user.
 */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $companyId = null;
            $userId = null;

            // Prefer explicit tenant helpers if present
            if (function_exists('company')) {
                try {
                    $c = company();
                    $companyId = $c->id ?? null;
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            if (function_exists('user')) {
                try {
                    $u = user();
                    if ($u) {
                        // In some builds company_id == user_id; we still enforce both columns.
                        $companyId = $companyId ?? ($u->company_id ?? $u->id ?? null);
                        $userId = $u->id ?? null;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            $table = $builder->getModel()->getTable();

            if ($companyId) {
                $builder->where($table . '.company_id', $companyId);
            }

            if ($userId) {
                $builder->where($table . '.user_id', $userId);
            }
        });

        static::creating(function ($model) {
            $companyId = $model->company_id ?? null;
            $userId = $model->user_id ?? null;

            if (function_exists('user')) {
                try {
                    $u = user();
                    if ($u) {
                        $companyId = $companyId ?? ($u->company_id ?? $u->id ?? null);
                        $userId = $userId ?? ($u->id ?? null);
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            if (!$companyId && function_exists('company')) {
                try {
                    $c = company();
                    $companyId = $c->id ?? null;
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            if (empty($model->company_id) && $companyId) {
                $model->company_id = $companyId;
            }

            if (empty($model->user_id) && $userId) {
                $model->user_id = $userId;
            }
        });
    }
}
