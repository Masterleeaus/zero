<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;
use Illuminate\Support\Facades\Event;

class DocumentsCreatePackHandler
{
    public function handle(WorkflowRunStep $step): void
    {
        // Governance: Documents is canonical. Workflow requests pack creation/attachment.
        Event::dispatch('documents.workflow.request_pack', [
            'workflow_run_step_id' => $step->id,
            'workflow_id' => $step->workflow_id,
            'config' => $step->config ?? [],
        ]);
    }
}
