<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Entities\WorkflowRun;

class WorkflowRunRecorder
{
    public function start(int $workflowId, array $context): WorkflowRun
    {
        return WorkflowRun::create([
            'workflow_id' => $workflowId,
            'company_id' => $context['company_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'event_name' => $context['event_name'] ?? 'manual',
            'event_payload' => $context['event_payload'] ?? null,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }
}
