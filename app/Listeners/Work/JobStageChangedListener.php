<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\JobStageChanged;
use App\Services\FSM\KanbanStatusService;
use App\Services\Work\JobBillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to job stage changes.
 *
 * When a job moves to an invoiceable stage and is marked billable,
 * trigger the billing pipeline to auto-generate an invoice.
 *
 * Also refreshes the kanban intelligence meta so readiness flags remain
 * accurate after every stage transition (Module 23).
 */
class JobStageChangedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(
        private readonly JobBillingService $billingService,
        private readonly KanbanStatusService $kanbanService,
    ) {}

    public function handle(JobStageChanged $event): void
    {
        $job   = $event->job;
        $stage = $event->newStage;

        try {
            // When moving into an invoiceable stage on a billable job,
            // handle the completed-billable flow
            if ($stage->is_invoiceable && $job->is_billable && ! $job->invoice_id) {
                $this->billingService->handleJobCompletedBillable($job);
            }

            // Record agreement consumption when the stage is closed
            if ($stage->is_closed && $job->agreement_id && $job->agreement) {
                $this->billingService->recordAgreementConsumption($job->agreement, $job);
            }

            // Module 23 — refresh kanban intelligence after every stage change
            $this->kanbanService->refresh($job);
        } catch (\Throwable $th) {
            Log::error('JobStageChangedListener: ' . $th->getMessage(), [
                'job_id'   => $job->id,
                'stage_id' => $stage->id,
            ]);
        }
    }
}
