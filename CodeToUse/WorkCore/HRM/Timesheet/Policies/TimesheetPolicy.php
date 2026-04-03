<?php

namespace Modules\Timesheet\Policies;

use App\Models\User;
use Modules\Timesheet\Entities\Timesheet;

class TimesheetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAbleTo('timesheet manage');
    }

    public function create(User $user): bool
    {
        return $user->isAbleTo('timesheet create');
    }

    public function update(User $user, Timesheet $timesheet): bool
    {
        return $user->isAbleTo('timesheet edit') && (int)$timesheet->created_by === (int)creatorId();
    }

    public function delete(User $user, Timesheet $timesheet): bool
    {
        return $user->isAbleTo('timesheet delete') && (int)$timesheet->created_by === (int)creatorId();
    }
}
