<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Work\WeeklyTimesheet;

class TimesheetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, WeeklyTimesheet $timesheet): bool
    {
        return $timesheet->company_id === $user->company_id;
    }

    public function update(User $user, WeeklyTimesheet $timesheet): bool
    {
        return $timesheet->company_id === $user->company_id
            && $timesheet->user_id === $user->id;
    }

    public function submit(User $user, WeeklyTimesheet $timesheet): bool
    {
        return $timesheet->company_id === $user->company_id
            && $timesheet->user_id === $user->id
            && in_array($timesheet->status, ['pending', 'draft'], true);
    }

    public function approve(User $user, WeeklyTimesheet $timesheet): bool
    {
        return $timesheet->company_id === $user->company_id
            && $user->isAdmin();
    }

    public function reject(User $user, WeeklyTimesheet $timesheet): bool
    {
        return $timesheet->company_id === $user->company_id
            && $user->isAdmin();
    }
}
