<?php
namespace Modules\PropertyManagement\Listeners;

use Modules\PropertyManagement\Events\PropertyVisitScheduled;
use Modules\PropertyManagement\Integrations\Core\TaskAdapterInterface;
use Modules\PropertyManagement\Integrations\Core\HrAdapterInterface;

class SyncCoreTaskOnVisitScheduled
{
    public function __construct(
        protected TaskAdapterInterface $tasks,
        protected HrAdapterInterface $hr
    ) {}

    public function handle(PropertyVisitScheduled $event): void
    {
        // Optional: create/update a core task
        $this->tasks->upsertTaskForVisit($event->visit);

        // Optional: inform HR roster/assignment
        $this->hr->reflectAssignment($event->visit);
    }
}
