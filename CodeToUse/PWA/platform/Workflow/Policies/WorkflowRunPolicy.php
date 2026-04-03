<?php

namespace Modules\Workflow\Policies;

use App\Models\User;
use Modules\Workflow\Entities\WorkflowRun;

class WorkflowRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->permission('view_workflow') === 'all' || $user->permission('manage_workflow') === 'all';
    }

    public function view(User $user, WorkflowRun $run): bool
    {
        return $this->viewAny($user);
    }
}
