<?php

namespace Modules\Workflow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Workflow\Services\Handlers\ActionHandlerManager;

class RunWorkflowActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $workflowRunId,
        public int $stepIndex,
        public string $actionKey,
        public array $actionConfig,
        public array $payload,
        public array $meta = []
    ) {}

    public function handle(ActionHandlerManager $manager): void
    {
        $manager->execute($this->workflowRunId, $this->stepIndex, $this->actionKey, $this->actionConfig, $this->payload, $this->meta);
    }
}
