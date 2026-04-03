<?php

declare(strict_types=1);

namespace App\Services\HRM;

use App\Events\Work\TimesheetApproved;
use App\Events\Work\TimesheetRejected;
use App\Events\Work\TimesheetSubmitted;
use App\Models\User;
use App\Models\Work\Timelog;
use App\Models\Work\WeeklyTimesheet;

class TimesheetService
{
    /**
     * Submit a weekly timesheet on behalf of a user.
     * Calculates total hours from timelogs, sets status to submitted, fires event.
     */
    public function submitWeeklyTimesheet(WeeklyTimesheet $sheet, User $user): bool
    {
        if (! in_array($sheet->status, ['pending', 'draft'], true)) {
            return false;
        }

        $totalHours = $this->calculateWeekHours(
            $sheet->user_id,
            $sheet->week_start->toDateString(),
            $sheet->week_end->toDateString(),
        );

        $sheet->update([
            'status'      => 'submitted',
            'total_hours' => $totalHours,
        ]);

        TimesheetSubmitted::dispatch($sheet);

        return true;
    }

    /**
     * Approve a submitted timesheet.
     */
    public function approveTimesheet(WeeklyTimesheet $sheet, User $reviewer): bool
    {
        if ($sheet->status !== 'submitted') {
            return false;
        }

        $sheet->update([
            'status'      => 'approved',
            'approved_by' => $reviewer->id,
            'approved_at' => now(),
        ]);

        TimesheetApproved::dispatch($sheet, $reviewer);

        return true;
    }

    /**
     * Reject a submitted timesheet.
     */
    public function rejectTimesheet(WeeklyTimesheet $sheet, User $reviewer, string $notes = ''): bool
    {
        if ($sheet->status !== 'submitted') {
            return false;
        }

        $sheet->update([
            'status' => 'rejected',
        ]);

        TimesheetRejected::dispatch($sheet, $reviewer);

        return true;
    }

    /**
     * Sum Timelog duration_minutes for a user within a date range and return as hours.
     */
    public function calculateWeekHours(int $userId, string $weekStart, string $weekEnd): float
    {
        $minutes = Timelog::query()
            ->withoutGlobalScope('company')
            ->where('user_id', $userId)
            ->whereBetween('started_at', [$weekStart, $weekEnd . ' 23:59:59'])
            ->sum('duration_minutes');

        return round($minutes / 60, 2);
    }
}
