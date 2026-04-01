<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\JobCompleted;
use App\Services\Work\JobBillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to job completion events.
 *
 * Triggers the completed-billable billing flow for billable jobs that
 * have not yet been invoiced, and records agreement service consumption.
 */
class JobCompletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly JobBillingService $billingService) {}

    public function handle(JobCompleted $event): void
    {
        $job = $event->job;

        try {
            if ($job->is_billable && ! $job->invoice_id) {
                $this->billingService->handleJobCompletedBillable($job);
            }

            if ($job->agreement_id && $job->agreement) {
                $this->billingService->recordAgreementConsumption($job->agreement, $job);
            }
        } catch (\Throwable $th) {
            Log::error('JobCompletedListener: ' . $th->getMessage(), ['job_id' => $job->id]);
        }
    }
}
