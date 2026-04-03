<?php

namespace Modules\Timesheet\Policies;

use App\Models\User;
use Modules\Timesheet\Entities\TimesheetSubmission;

class TimesheetSubmissionPolicy
{
    public function view(User $user, TimesheetSubmission $submission): bool
    {
        return $user->id === (int) $submission->user_id || $this->canApprove($user, $submission);
    }

    public function submit(User $user): bool
    {
        return $user->isAbleTo('timesheet manage');
    }

    public function approve(User $user, TimesheetSubmission $submission): bool
    {
        return $this->canApprove($user, $submission);
    }

    protected function canApprove(User $user, TimesheetSubmission $submission): bool
    {
        if ($user->isAbleTo('timesheet approve')) {
            return true;
        }

        // Optional: "crew lead" role support via config mapping
        $leadRole = config('timesheet.approvals.crew_lead_role', null);
        if ($leadRole && method_exists($user, 'hasRole') && $user->hasRole($leadRole)) {
            return true;
        }

        return false;
    }
}
