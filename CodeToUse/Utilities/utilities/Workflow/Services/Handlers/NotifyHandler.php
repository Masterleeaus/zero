<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;

class NotifyHandler
{
    public function handle(WorkflowRunStep $step): void
    {
        // Example: send a notification using config payload
        // $config = $step->config;
        // Notification::route('mail', $config['email'])->notify(new WorkflowNotice(...));
        // For now, just simulate work:
        usleep(10000);
    }
}
