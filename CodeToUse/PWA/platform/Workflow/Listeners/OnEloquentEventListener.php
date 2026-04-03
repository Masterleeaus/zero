<?php

namespace Modules\Workflow\Listeners;

use Illuminate\Database\Eloquent\Model;
use Modules\Workflow\Services\WorkflowEventDispatcher;

class OnEloquentEventListener
{
    public function __construct(protected WorkflowEventDispatcher $dispatcher)
    {
    }

    public function handleCreated(string $eventName, array $data = []): void
    {
        $this->handle('created', $eventName, $data);
    }

    public function handleUpdated(string $eventName, array $data = []): void
    {
        $this->handle('updated', $eventName, $data);
    }

    public function handleDeleted(string $eventName, array $data = []): void
    {
        $this->handle('deleted', $eventName, $data);
    }

    protected function handle(string $op, string $eventName, array $data): void
    {
        // Laravel emits: [0 => Model]
        $model = $data[0] ?? null;
        if (!$model instanceof Model) {
            return;
        }

        $this->dispatcher->dispatchEloquentEvent($op, $model);
    }
}
