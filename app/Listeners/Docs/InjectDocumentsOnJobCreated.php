<?php

declare(strict_types=1);

namespace App\Listeners\Docs;

use App\Events\Work\JobCreated;
use App\Services\Docs\DocsExecutionBridgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

class InjectDocumentsOnJobCreated implements ShouldQueue
{
    public function __construct(
        private readonly DocsExecutionBridgeService $bridge,
    ) {}

    public function handle(JobCreated $event): void
    {
        $this->bridge->injectForJob($event->job);
    }
}
