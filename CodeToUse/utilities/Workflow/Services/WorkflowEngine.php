<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Entities\Workflow;
use Modules\Workflow\Entities\WorkflowStep;
use Modules\Workflow\Entities\WorkflowLog;
use Modules\Workflow\Entities\WorkflowRun;
use Modules\Workflow\Entities\WorkflowRunStep;
use Illuminate\Support\Facades\Event;
use Throwable;

class WorkflowEngine
{
    public function runRunStep(WorkflowRunStep $step): void
    {
        $step->status = 'running';
        $step->started_at = now();
        $step->save();

        try {
            $handlerClass = $step->handler ?? null;
            if (!$handlerClass || !class_exists($handlerClass)) {
                throw new \RuntimeException('Handler not found: ' . ($handlerClass ?: 'null'));
            }

            $handler = app($handlerClass);
            $handler->handle($step);

            $step->status = 'done';
            $step->completed_at = now();
            $step->save();

            WorkflowLog::create([
                'workflow_id' => $step->workflow_id,
                'step_id' => null,
                'level' => 'info',
                'message' => 'Run step completed',
                'context' => ['handler' => $handlerClass, 'workflow_run_step_id' => $step->id, 'workflow_run_id' => $step->workflow_run_id],
            ]);
        } catch (Throwable $e) {
            $step->status = 'failed';
            $step->completed_at = now();
            $step->save();

            WorkflowLog::create([
                'workflow_id' => $step->workflow_id,
                'step_id' => null,
                'level' => 'error',
                'message' => $e->getMessage(),
                'context' => ['handler' => ($step->handler ?? null), 'workflow_run_step_id' => $step->id, 'workflow_run_id' => $step->workflow_run_id],
            ]);

            Event::dispatch('workflow.run_step.failed', [$step, $e]);
            throw $e;
        }
    }

    public function runWorkflow(int $workflowId, array $context = []): WorkflowRun
    {
        $workflow = Workflow::findOrFail($workflowId);

        $run = WorkflowRun::create([
            'workflow_id' => $workflow->id,
            'company_id' => $context['company_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'event_name' => $context['event_name'] ?? 'manual',
            'event_payload' => $context['event_payload'] ?? null,
            'status' => 'running',
            'started_at' => now(),
        ]);

        // Clone template steps into run steps
        $templates = WorkflowStep::where('workflow_id', $workflowId)->orderBy('position')->get();
        foreach ($templates as $tpl) {
            WorkflowRunStep::create([
                'workflow_run_id' => $run->id,
                'workflow_id' => $workflowId,
                'position' => $tpl->position,
                'type' => $tpl->type,
                'handler' => $tpl->handler,
                'config' => $tpl->config,
                'status' => 'pending',
            ]);
        }

        $steps = WorkflowRunStep::where('workflow_run_id', $run->id)->orderBy('position')->get();
        foreach ($steps as $step) {
            $this->runRunStep($step);
        }

        $run->status = 'done';
        $run->completed_at = now();
        $run->save();

        Event::dispatch('workflow.completed', [$workflowId, $run->id]);

        return $run;
    }
}
