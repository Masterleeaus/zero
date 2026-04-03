<?php

namespace Modules\TitanHello\Services\Inbox;

use Illuminate\Database\Eloquent\Builder;
use Modules\TitanHello\Models\Call;

class CallInboxQueryService
{
    public function query(array $filters = []): Builder
    {
        $q = Call::query();

        if (!empty($filters['company_id'])) {
            $q->where('company_id', (int) $filters['company_id']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', (string) $filters['status']);
        }

        if (!empty($filters['direction'])) {
            $q->where('direction', (string) $filters['direction']);
        }

        if (!empty($filters['disposition'])) {
            $q->where('disposition', (string) $filters['disposition']);
        }

        if (isset($filters['assigned']) && $filters['assigned'] !== '') {
            if ($filters['assigned'] === 'unassigned') {
                $q->whereNull('assigned_to_user_id');
            } elseif ($filters['assigned'] === 'me' && !empty($filters['user_id'])) {
                $q->where('assigned_to_user_id', (int) $filters['user_id']);
            } else {
                $q->where('assigned_to_user_id', (int) $filters['assigned']);
            }
        }

        if (!empty($filters['q'])) {
            $term = trim((string) $filters['q']);
            $q->where(function (Builder $sub) use ($term) {
                $sub->where('from_number', 'like', "%{$term}%")
                    ->orWhere('to_number', 'like', "%{$term}%")
                    ->orWhere('provider_call_sid', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $q->whereDate('created_at', '>=', (string) $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $q->whereDate('created_at', '<=', (string) $filters['date_to']);
        }

        return $q;
    }
}
