<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\ServiceJobRescheduled;
use App\Services\Calendar\BusinessSuiteCalendarAdapter;
use App\Services\Scheduling\SchedulingSurfaceProvider;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Updates the Business Suite calendar surface when a ServiceJob is rescheduled.
 *
 * Removes the stale entry by the old key then pushes the updated event.
 *
 * Module 9 (fieldservice_calendar) — calendar lifecycle listener.
 */
class ServiceJobRescheduledListener implements ShouldQueue
{
    public function __construct(
        private readonly BusinessSuiteCalendarAdapter $calendarAdapter,
        private readonly SchedulingSurfaceProvider $surfaceProvider,
    ) {}

    public function handle(ServiceJobRescheduled $event): void
    {
        $job = $event->job;

        $dto = $this->surfaceProvider->normaliseEntity($job, $job->premises_id, $job->customer_id);

        // Remove stale entry by previous key if start changed, then push updated event.
        if ($event->previousStart !== null) {
            $oldKey = $job->getSchedulableType() . ':' . $job->getKey();
            $this->calendarAdapter->remove($oldKey);
        }

        $this->calendarAdapter->broadcast($dto);
    }

}
