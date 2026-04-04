<?php

declare(strict_types=1);

namespace App\Services\HRM;

use App\Models\Work\Attendance;
use App\Models\Work\Leave;
use Carbon\Carbon;

class PayrollInputService
{
    public function calculateWeeklyHours(int $userId, string $weekStart, string $weekEnd): float
    {
        $totalMinutes = Attendance::query()
            ->where('user_id', $userId)
            ->whereNotNull('check_in_at')
            ->whereNotNull('check_out_at')
            ->whereBetween('check_in_at', [$weekStart . ' 00:00:00', $weekEnd . ' 23:59:59'])
            ->sum('duration_minutes');

        return round((float) $totalMinutes / 60, 2);
    }

    public function calculateOvertime(int $userId, string $weekStart, string $weekEnd, float $standardHoursPerWeek = 40.0): float
    {
        $worked = $this->calculateWeeklyHours($userId, $weekStart, $weekEnd);

        return max(0.0, round($worked - $standardHoursPerWeek, 2));
    }

    public function calculateLeaveHours(int $userId, string $periodStart, string $periodEnd, float $hoursPerDay = 8.0): float
    {
        $leaves = Leave::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $periodEnd)
            ->where('end_date', '>=', $periodStart)
            ->get();

        $totalDays = 0.0;

        foreach ($leaves as $leave) {
            $start = Carbon::parse($leave->start_date)->max(Carbon::parse($periodStart));
            $end   = Carbon::parse($leave->end_date)->min(Carbon::parse($periodEnd));
            if ($end->gte($start)) {
                $totalDays += $start->diffInDays($end) + 1;
            }
        }

        return round($totalDays * $hoursPerDay, 2);
    }

    public function calculatePayableHours(int $userId, string $periodStart, string $periodEnd): array
    {
        $worked   = $this->calculateWeeklyHours($userId, $periodStart, $periodEnd);
        $overtime = $this->calculateOvertime($userId, $periodStart, $periodEnd);
        $leave    = $this->calculateLeaveHours($userId, $periodStart, $periodEnd);
        $regular  = round(max(0.0, $worked - $overtime), 2);

        return [
            'regular'  => $regular,
            'overtime' => $overtime,
            'leave'    => $leave,
            'total'    => round($regular + $overtime + $leave, 2),
        ];
    }
}
