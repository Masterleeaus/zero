<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\ServiceJobScheduled;
use App\Services\Calendar\BusinessSuiteCalendarAdapter;
use App\Services\Scheduling\SchedulingSurfaceProvider;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Syncs a newly scheduled ServiceJob to the Business Suite calendar surface.
 *
 * Module 9 (fieldservice_calendar) — calendar lifecycle listener.
 */
class ServiceJobScheduledListener implements ShouldQueue
{
    public function __construct(
        private readonly BusinessSuiteCalendarAdapter $calendarAdapter,
        private readonly SchedulingSurfaceProvider $surfaceProvider,
    ) {}

    public function handle(ServiceJobScheduled $event): void
    {
        $job = $event->job;

        $dto = $this->surfaceProvider->normaliseEntity($job, $job->premises_id, $job->customer_id);

        $this->calendarAdapter->broadcast($dto);
    }

}
