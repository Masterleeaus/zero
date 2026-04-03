<?php

namespace Modules\Performance\Services\Reports;

use Modules\Performance\Entities\JobPerformanceSnapshot;

class SitePerformanceReportService
{
    public function build(array $filters): array
    {
        $q = JobPerformanceSnapshot::query();
        if (!empty($filters['from'])) $q->whereDate('created_at', '>=', $filters['from']);
        if (!empty($filters['to'])) $q->whereDate('created_at', '<=', $filters['to']);

        $items = $q->get()->groupBy('project_id');

        $rows = [];
        foreach ($items as $projectId => $group) {
            $rows[] = [
                'project_id' => $projectId,
                'jobs' => $group->count(),
                'avg_overall' => round((float)$group->avg('overall_score'), 2),
                'avg_safety' => round((float)$group->avg('safety_score'), 2),
                'callbacks' => (int)$group->sum('callback_count'),
            ];
        }

        usort($rows, fn($a,$b) => ($a['avg_overall'] <=> $b['avg_overall']));
        return ['rows' => $rows];
    }
}
