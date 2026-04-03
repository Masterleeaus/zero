<?php

namespace Modules\Performance\Services\Reports;

use Modules\Performance\Entities\JobPerformanceSnapshot;
use Illuminate\Support\Carbon;

class CallbackTrendReportService
{
    public function build(array $filters): array
    {
        $q = JobPerformanceSnapshot::query();
        if (!empty($filters['from'])) $q->whereDate('created_at', '>=', $filters['from']);
        if (!empty($filters['to'])) $q->whereDate('created_at', '<=', $filters['to']);
        if (!empty($filters['project_id'])) $q->where('project_id', $filters['project_id']);
        if (!empty($filters['user_id'])) $q->where('user_id', $filters['user_id']);

        $items = $q->get();

        $byMonth = [];
        foreach ($items as $s) {
            $k = $s->created_at ? $s->created_at->format('Y-m') : 'unknown';
            $byMonth[$k] = ($byMonth[$k] ?? 0) + (int)$s->callback_count;
        }
        ksort($byMonth);

        return [
            'series' => $byMonth,
            'total_callbacks' => (int)$items->sum('callback_count'),
            'count' => $items->count(),
        ];
    }

    public function exportRows(array $filters): array
    {
        $r = $this->build($filters);
        $rows = [];
        foreach ($r['series'] as $month => $count) {
            $rows[] = ['month' => $month, 'callback_count' => $count];
        }
        return $rows ?: [['no_data' => 'no_data']];
    }
}
