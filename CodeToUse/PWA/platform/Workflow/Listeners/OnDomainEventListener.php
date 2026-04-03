<?php

namespace Modules\Workflow\Listeners;

use Modules\Workflow\Services\WorkflowEventDispatcher;

/**
 * Receives workflow.domain.* events and forwards them into the workflow engine.
 */
class OnDomainEventListener
{
    public function __construct(protected WorkflowEventDispatcher $dispatcher)
    {
    }

    /**
     * @param string $eventName The concrete event name (e.g. workflow.domain.inspection.completed)
     * @param array $data Event payload
     */
    public function handle(string $eventName, array $data = []): void
    {
        // Strip the prefix so triggers can match on "inspection.completed"
        $key = preg_replace('/^workflow\.domain\./', '', $eventName);

        $this->dispatcher->dispatchDomainEvent($key, $data);
    }
}
