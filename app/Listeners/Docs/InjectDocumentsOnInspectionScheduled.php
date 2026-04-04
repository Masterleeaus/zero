<?php

declare(strict_types=1);

namespace App\Listeners\Docs;

use App\Events\Inspection\InspectionScheduled;
use App\Services\Docs\DocsExecutionBridgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

class InjectDocumentsOnInspectionScheduled implements ShouldQueue
{
    public function __construct(
        private readonly DocsExecutionBridgeService $bridge,
    ) {}

    public function handle(InspectionScheduled $event): void
    {
        $this->bridge->injectForInspection($event->inspection);
    }
}
