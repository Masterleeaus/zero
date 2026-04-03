<?php

namespace App\Listeners\Work;

use App\Events\Work\JobDispatched;

class NotifyTechnicianOfAssignment
{
    public function handle(JobDispatched $event): void
    {
        // Notification dispatch — stubbed for Phase 2 comms integration
        // Future: $event->assignment->technician->notify(new JobAssignedNotification($event->job));
    }
}
