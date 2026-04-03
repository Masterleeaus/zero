<?php

namespace Modules\Workflow\Services\Diagnostics;

use Illuminate\Support\Facades\Auth;

class PermissionHealth
{
    public function report(): array
    {
        $user = Auth::user();
        if (!$user) return ['auth' => false];

        return [
            'auth' => true,
            'can_workflow_view' => $user->can('workflow.view'),
            'can_workflow_create' => $user->can('workflow.create'),
            'can_workflow_run' => $user->can('workflow.run'),
            'can_workflow_admin' => $user->can('workflow.admin'),
        ];
    }
}
