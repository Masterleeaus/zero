<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;
use Illuminate\Support\Facades\Event;

class CustomerConnectSendHandler
{
    public function handle(WorkflowRunStep $step): void
    {
        // Governance: Workflow never sends directly. It requests orchestration via CustomerConnect.
        // Step config expected:
        // - template_key OR message
        // - to (client/lead/user identifiers or raw)
        // - channel (sms,email,whatsapp,etc)
        Event::dispatch('customerconnect.workflow.request_send', [
            'workflow_run_step_id' => $step->id,
            'workflow_id' => $step->workflow_id,
            'config' => $step->config ?? [],
        ]);
    }
}
