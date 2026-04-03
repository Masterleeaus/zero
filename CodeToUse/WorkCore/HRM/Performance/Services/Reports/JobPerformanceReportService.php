<?php

namespace Modules\Performance\Services\Reports;

use Modules\Performance\Entities\JobPerformanceSnapshot;

class JobPerformanceReportService
{
    public function build(array $filters): array
    {
        $q = JobPerformanceSnapshot::query();

        if (!empty($filters['from'])) $q->whereDate('created_at', '>=', $filters['from']);
        if (!empty($filters['to'])) $q->whereDate('created_at', '<=', $filters['to']);
        if (!empty($filters['project_id'])) $q->where('project_id', $filters['project_id']);
        if (!empty($filters['user_id'])) $q->where('user_id', $filters['user_id']);

        $items = $q->get();

        return [
            'count' => $items->count(),
            'avg_overall' => round((float)$items->avg('overall_score'), 2),
            'avg_quality' => round((float)$items->avg('quality_score'), 2),
            'avg_safety' => round((float)$items->avg('safety_score'), 2),
            'avg_timeliness' => round((float)$items->avg('timeliness_score'), 2),
            'avg_documentation' => round((float)$items->avg('documentation_score'), 2),
            'avg_rating' => round((float)$items->avg('customer_rating'), 2),
            'total_callbacks' => (int)$items->sum('callback_count'),
            'items' => $items,
        ];
    }

    public function exportRows(array $filters): array
    {
        $report = $this->build($filters);
        $rows = [];
        foreach ($report['items'] as $s) {
            $rows[] = [
                'snapshot_id' => $s->id,
                'objective_id' => $s->objective_id,
                'user_id' => $s->user_id,
                'project_id' => $s->project_id,
                'overall_score' => $s->overall_score,
                'quality_score' => $s->quality_score,
                'safety_score' => $s->safety_score,
                'timeliness_score' => $s->timeliness_score,
                'documentation_score' => $s->documentation_score,
                'callback_count' => $s->callback_count,
                'customer_rating' => $s->customer_rating,
                'status' => $s->status,
                'created_at' => (string)$s->created_at,
            ];
        }
        return $rows ?: [['no_data' => 'no_data']];
    }
}
