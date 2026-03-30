<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Jobs\RunWorkflowJob;

class WorkflowDispatcher
{
    /**
     * Dispatch workflow evaluation/run to queue.
     */
    public function dispatch(string $triggerKey, array $payload, array $meta = []): void
    {
        RunWorkflowJob::dispatch($triggerKey, $payload, $meta)->onQueue('workflows');
    }
}
