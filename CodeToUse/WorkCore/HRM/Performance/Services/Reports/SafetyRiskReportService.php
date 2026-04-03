<?php

namespace Modules\Performance\Services\Reports;

use Modules\Performance\Entities\JobPerformanceSnapshot;

class SafetyRiskReportService
{
    public function build(array $filters): array
    {
        $q = JobPerformanceSnapshot::query();

        if (!empty($filters['from'])) $q->whereDate('created_at', '>=', $filters['from']);
        if (!empty($filters['to'])) $q->whereDate('created_at', '<=', $filters['to']);
        if (!empty($filters['project_id'])) $q->where('project_id', $filters['project_id']);
        if (!empty($filters['user_id'])) $q->where('user_id', $filters['user_id']);

        $items = $q->orderBy('safety_score')->limit(200)->get();

        return [
            'lowest' => $items->take(20),
            'avg_safety' => round((float)$items->avg('safety_score'), 2),
            'count' => $items->count(),
        ];
    }
}
