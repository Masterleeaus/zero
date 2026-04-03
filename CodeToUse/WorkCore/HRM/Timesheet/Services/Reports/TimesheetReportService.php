<?php

namespace Modules\Timesheet\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Timesheet\Entities\Timesheet;

class TimesheetReportService
{
    public function summaryForRange(?int $companyId, ?int $userId, Carbon $from, Carbon $to): array
    {
        $q = Timesheet::query()->forCreator()->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        if ($companyId) {
            $q->where('company_id', $companyId);
        }
        if ($userId) {
            $q->where('user_id', $userId);
        }

        $rows = $q->get(['hours','minutes','fsm_cost_total']);

        $minutes = 0;
        $cost = 0.0;
        foreach ($rows as $r) {
            $minutes += ((int) $r->hours) * 60 + ((int) $r->minutes);
            $cost += (float) ($r->fsm_cost_total ?? 0);
        }

        return [
            'entries' => $rows->count(),
            'minutes' => $minutes,
            'hours_decimal' => round($minutes / 60, 2),
            'cost_total' => round($cost, 2),
        ];
    }

    public function breakdownByProject(Carbon $from, Carbon $to, ?int $companyId = null): array
    {
        $q = Timesheet::query()->forCreator()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('project_id');

        if ($companyId) {
            $q->where('company_id', $companyId);
        }

        $rows = $q->select([
                'project_id',
                DB::raw('COUNT(*) as entries'),
                DB::raw('SUM(hours*60 + minutes) as total_minutes'),
                DB::raw('SUM(COALESCE(fsm_cost_total,0)) as cost_total'),
            ])
            ->groupBy('project_id')
            ->orderByDesc('total_minutes')
            ->limit(100)
            ->get();

        return $rows->map(fn($r) => [
            'project_id' => (int) $r->project_id,
            'entries' => (int) $r->entries,
            'minutes' => (int) $r->total_minutes,
            'hours_decimal' => round(((int)$r->total_minutes) / 60, 2),
            'cost_total' => round((float)$r->cost_total, 2),
        ])->all();
    }

    
    public function breakdownByUser(Carbon $from, Carbon $to, ?int $companyId = null): array
    {
        $q = Timesheet::query()->forCreator()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        if ($companyId) {
            $q->where('company_id', $companyId);
        }

        $rows = $q->select([
                'user_id',
                DB::raw('COUNT(*) as entries'),
                DB::raw('SUM(hours*60 + minutes) as total_minutes'),
                DB::raw('SUM(COALESCE(fsm_cost_total,0)) as cost_total'),
            ])
            ->groupBy('user_id')
            ->orderByDesc('total_minutes')
            ->limit(200)
            ->get();

        return $rows->map(fn($r) => [
            'user_id' => (int) $r->user_id,
            'entries' => (int) $r->entries,
            'minutes' => (int) $r->total_minutes,
            'hours_decimal' => round(((int)$r->total_minutes) / 60, 2),
            'cost_total' => round((float)$r->cost_total, 2),
        ])->all();
    }

    public function breakdownByWorkOrder(Carbon $from, Carbon $to, ?int $companyId = null): array
    {
        $q = Timesheet::query()->forCreator()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('work_order_id');

        if ($companyId) {
            $q->where('company_id', $companyId);
        }

        $rows = $q->select([
                'work_order_id',
                DB::raw('COUNT(*) as entries'),
                DB::raw('SUM(hours*60 + minutes) as total_minutes'),
                DB::raw('SUM(COALESCE(fsm_cost_total,0)) as cost_total'),
            ])
            ->groupBy('work_order_id')
            ->orderByDesc('total_minutes')
            ->limit(100)
            ->get();

        return $rows->map(fn($r) => [
            'work_order_id' => $r->work_order_id,
            'entries' => (int) $r->entries,
            'minutes' => (int) $r->total_minutes,
            'hours_decimal' => round(((int)$r->total_minutes) / 60, 2),
            'cost_total' => round((float)$r->cost_total, 2),
        ])->all();
    }
}
