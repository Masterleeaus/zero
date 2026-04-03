<?php

namespace Modules\Workflow\Policies;

use App\Models\User;
use Modules\Workflow\Entities\Workflow;

class WorkflowPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->permission('view_workflow') === 'all' || $user->permission('manage_workflow') === 'all';
    }

    public function view(User $user, Workflow $workflow): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->permission('manage_workflow') === 'all';
    }

    public function update(User $user, Workflow $workflow): bool
    {
        return $user->permission('manage_workflow') === 'all';
    }

    public function run(User $user, Workflow $workflow): bool
    {
        return $user->permission('manage_workflow') === 'all';
    }
}
