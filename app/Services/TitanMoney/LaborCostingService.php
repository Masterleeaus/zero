<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\JobCostAllocation;
use App\Models\Work\ServiceJob;
use App\Models\Work\StaffProfile;
use App\Models\Work\TimesheetSubmission;
use App\Models\User;
use Carbon\Carbon;

class LaborCostingService
{
    public function __construct(protected JobCostingService $jobCosting) {}

    public function costForTimesheetSubmission(TimesheetSubmission $submission): array
    {
        $profile    = StaffProfile::where('user_id', $submission->user_id)->first();
        $hourlyRate = (float) ($profile?->hourly_rate ?? 0);
        $hours      = (float) ($submission->total_hours ?? 0);
        $cost       = round($hours * $hourlyRate, 2);

        return [
            'hours'   => $hours,
            'rate'    => $hourlyRate,
            'cost'    => $cost,
            'user_id' => $submission->user_id,
        ];
    }

    public function costForUser(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $profile    = StaffProfile::where('user_id', $user->id)->first();
        $hourlyRate = (float) ($profile?->hourly_rate ?? 0);

        $submissions = TimesheetSubmission::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('week_start', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $totalHours = $submissions->sum('total_hours');
        $totalCost  = round((float) $totalHours * $hourlyRate, 2);

        return [
            'user_id'      => $user->id,
            'hours'        => (float) $totalHours,
            'rate'         => $hourlyRate,
            'cost'         => $totalCost,
            'submissions'  => $submissions->count(),
        ];
    }

    public function costForTeam(int $teamId, Carbon $from, Carbon $to): array
    {
        $submissions = TimesheetSubmission::query()
            ->where('team_id', $teamId)
            ->where('status', 'approved')
            ->whereBetween('week_start', [$from->toDateString(), $to->toDateString()])
            ->with('user')
            ->get();

        $byUser = [];
        foreach ($submissions as $submission) {
            $userId = $submission->user_id;
            if (! isset($byUser[$userId])) {
                $profile            = StaffProfile::where('user_id', $userId)->first();
                $byUser[$userId]    = [
                    'user_id' => $userId,
                    'hours'   => 0.0,
                    'rate'    => (float) ($profile?->hourly_rate ?? 0),
                    'cost'    => 0.0,
                ];
            }
            $byUser[$userId]['hours'] += (float) $submission->total_hours;
        }

        foreach ($byUser as &$row) {
            $row['cost'] = round($row['hours'] * $row['rate'], 2);
        }

        return array_values($byUser);
    }

    public function costForJob(ServiceJob $job): float
    {
        return (float) JobCostAllocation::query()
            ->forJob($job->id)
            ->byCostType('labour')
            ->sum('amount');
    }

    public function allocateTimesheetToJob(TimesheetSubmission $submission, ServiceJob $job): JobCostAllocation
    {
        $profile    = StaffProfile::where('user_id', $submission->user_id)->first();
        $hourlyRate = (float) ($profile?->hourly_rate ?? 0);

        return $this->jobCosting->allocateTimesheetLabor($submission, $hourlyRate, $job->id);
    }
}
