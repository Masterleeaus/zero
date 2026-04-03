<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;
use Illuminate\Support\Facades\Event;

class AssetManagerMaintenanceHandler
{
    public function handle(WorkflowRunStep $step): void
    {
        // Requests Asset Manager to schedule/record maintenance.
        Event::dispatch('assetmanager.workflow.request_maintenance', [
            'workflow_run_step_id' => $step->id,
            'workflow_id' => $step->workflow_id,
            'config' => $step->config ?? [],
        ]);
    }
}
