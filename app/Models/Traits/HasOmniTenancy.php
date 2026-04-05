<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * HasOmniTenancy — opinionated tenant-scoping helpers for all Omni models.
 *
 * Builds on top of the host `BelongsToCompany` global-scope trait.
 * Provides named scopes that express Omni-domain intent rather than raw SQL.
 *
 * All Omni models that use `BelongsToCompany` should also use this trait
 * to benefit from consistent, N+1-safe query helpers.
 */
trait HasOmniTenancy
{
    /**
     * Scope: active records for a specific company (bypasses global scope for cross-company admin).
     */
    public function scopeForOmniCompany(Builder $query, int $companyId): Builder
    {
        return $query->withoutGlobalScope('company')
            ->where($query->qualifyColumn('company_id'), $companyId);
    }

    /**
     * Scope: only records belonging to active (non-deleted) agents.
     */
    public function scopeWithActiveAgent(Builder $query): Builder
    {
        if ($this->getTable() !== 'omni_agents') {
            $query->whereHas('agent', fn ($q) => $q->where('is_active', true));
        }

        return $query;
    }

    /**
     * Scope: eager-load the full Omni conversation context (N+1 guard).
     * Useful for inbox / list views.
     */
    public function scopeWithConversationContext(Builder $query): Builder
    {
        return $query->with(['agent', 'omniCustomer']);
    }

    /**
     * Scope: records created within the last N days.
     */
    public function scopeCreatedWithin(Builder $query, int $days): Builder
    {
        return $query->where($query->qualifyColumn('created_at'), '>=', now()->subDays($days));
    }
}
