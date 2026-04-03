<?php

namespace Modules\Workflow\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Workflow\Entities\Workflow;
use Modules\Workflow\Entities\WorkflowLog;

class WorkflowEventDispatcher
{
    public function __construct(
        protected WorkflowEngine $engine,
        protected TriggerRegistry $triggers
    ) {}

    public function dispatchDomainEvent(string $key, array $payload = []): void
    {
        $this->handle($key, $payload);
    }

    public function dispatchEloquentEvent(string $op, Model $model): void
    {
        $key = 'eloquent.' . $op;

        $payload = [
            'model_class' => get_class($model),
            'model_id' => (int) ($model->getKey() ?? 0),
            'attributes' => $model->getAttributes(),
            'dirty' => method_exists($model, 'getDirty') ? $model->getDirty() : [],
            'company_id' => $model->getAttribute('company_id') ?? null,
            'user_id' => Auth::id(),
        ];

        $this->handle($key, $payload);
    }

    public function handle(string $eventKey, array $payload = []): void
    {
        $workflows = Workflow::query()
            ->where('is_active', 1)
            ->whereNotNull('trigger_event')
            ->get();

        foreach ($workflows as $wf) {
            if (!$this->triggers->matches($wf->trigger_event, $eventKey)) {
                continue;
            }

            try {
                $run = $this->engine->runWorkflow($wf->id, [
                    'company_id' => $payload['company_id'] ?? null,
                    'user_id' => $payload['user_id'] ?? Auth::id(),
                    'event_name' => $eventKey,
                    'event_payload' => $payload,
                ]);

                WorkflowLog::create([
                    'workflow_id' => $wf->id,
                    'step_id' => null,
                    'level' => 'info',
                    'message' => 'Workflow run executed.',
                    'context' => [
                        'run_id' => $run->id,
                        'event' => $eventKey,
                        'company_id' => $payload['company_id'] ?? null,
                        'user_id' => $payload['user_id'] ?? Auth::id(),
                    ],
                ]);
            } catch (\Throwable $e) {
                WorkflowLog::create([
                    'workflow_id' => $wf->id,
                    'step_id' => null,
                    'level' => 'error',
                    'message' => 'Workflow run failed: ' . $e->getMessage(),
                    'context' => ['event' => $eventKey],
                ]);
            }
        }
    }
}
