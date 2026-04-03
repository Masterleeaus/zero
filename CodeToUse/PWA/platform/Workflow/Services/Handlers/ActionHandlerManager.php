<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;

class ActionHandlerManager
{
    /**
     * Execute an action handler in an isolated way (used by queue jobs).
     */
    public function execute(
        int $workflowRunId,
        int $stepIndex,
        string $actionKey,
        array $actionConfig,
        array $payload,
        array $meta = []
    ): void {
        $handlerClass = $actionConfig['handler'] ?? null;

        if (!$handlerClass || !class_exists($handlerClass)) {
            throw new \RuntimeException('Handler not found for action: ' . $actionKey);
        }

        // Construct a pseudo WorkflowRunStep-compatible model if needed:
        $step = WorkflowRunStep::where('workflow_run_id', $workflowRunId)
            ->where('position', $stepIndex)
            ->first();

        if ($step) {
            $handler = app($handlerClass);
            $handler->handle($step);
        }
    }
}
