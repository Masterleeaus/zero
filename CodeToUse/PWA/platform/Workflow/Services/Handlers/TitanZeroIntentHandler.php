<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;
use Illuminate\Support\Facades\Event;

class TitanZeroIntentHandler
{
    public function handle(WorkflowRunStep $step): void
    {
        // Governance: Titan Zero is the ONLY AI authority.
        // Workflow requests a structured intent/action via Titan Zero; it must log and save artifacts.
        Event::dispatch('titanzero.workflow.request_intent', [
            'workflow_run_step_id' => $step->id,
            'workflow_id' => $step->workflow_id,
            'config' => $step->config ?? [],
        ]);
    }
}
