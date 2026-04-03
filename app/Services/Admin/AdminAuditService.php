<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Admin\AdminAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * AdminAuditService
 *
 * Provides paginated, filterable access to the `tz_audit_log` table for the
 * Titan Admin panel. Company-scoped queries respect Titan tenancy doctrine.
 */
class AdminAuditService
{
    /**
     * Return a paginated audit log, optionally filtered.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = AdminAuditLog::query()
            ->with('performer')
            ->orderByDesc('created_at');

        if (! empty($filters['company_id'])) {
            $query->forCompany((int) $filters['company_id']);
        }

        if (! empty($filters['action'])) {
            $query->forAction($filters['action']);
        }

        if (! empty($filters['performed_by'])) {
            $query->where('performed_by', (int) $filters['performed_by']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Return a list of distinct action types present in the log.
     *
     * @return string[]
     */
    public function distinctActions(): array
    {
        return AdminAuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->all();
    }
}
