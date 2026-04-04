<?php

namespace App\Listeners\Money;

use App\Events\Money\TimesheetApproved;
use App\Services\TitanMoney\LaborCostingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostTimesheetApprovedToJobCost implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(protected LaborCostingService $laborCosting) {}

    public function handle(TimesheetApproved $event): void
    {
        $submission = $event->submission;
        if ($submission->service_job_id ?? null) {
            // Only auto-allocate if linked to a job
            // Larger automation can be wired here
        }
        \Log::info('TimesheetApproved event received', ['submission_id' => $submission->id]);
    }
}
