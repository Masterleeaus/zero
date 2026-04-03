<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\ServicePlanVisitCompleted;
use App\Models\Inspection\InspectionInstance;
use App\Models\Work\ChecklistRun;
use App\Models\Work\ServiceJob;
use App\Services\Work\AgreementSchedulerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to ServicePlanVisitCompleted events.
 *
 * Responsibilities:
 *   1. Auto-launch checklist runs if the service plan specifies checklists.
 *   2. Create an inspection followup instance if required.
 *   3. Record agreement usage / advance the service plan schedule.
 *   4. Update asset maintenance records for equipment at the premises.
 */
class ServicePlanVisitCompletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(
        private readonly AgreementSchedulerService $agreementScheduler,
    ) {}

    public function handle(ServicePlanVisitCompleted $event): void
    {
        $visit = $event->visit;

        try {
            $this->autoLaunchChecklists($visit);
            $this->createInspectionFollowup($visit);
            $this->trackAgreementUsage($visit);
            $this->updateAssetMaintenance($visit);
        } catch (\Throwable $th) {
            Log::error('ServicePlanVisitCompletedListener: ' . $th->getMessage(), [
                'visit_id' => $visit->id,
            ]);
        }
    }

    private function autoLaunchChecklists(\App\Models\Work\ServicePlanVisit $visit): void
    {
        $plan = $visit->plan;
        if (! $plan) {
            return;
        }

        foreach ($plan->checklists as $planChecklist) {
            ChecklistRun::create([
                'company_id'            => $visit->company_id,
                'created_by'            => $visit->created_by,
                'checklist_template_id' => $planChecklist->checklist_template_id,
                'runnable_type'         => \App\Models\Work\ServicePlanVisit::class,
                'runnable_id'           => $visit->id,
                'title'                 => $planChecklist->title ?? $plan->name . ' checklist',
                'status'                => 'pending',
            ]);
        }

        Log::info('ChecklistAutoLaunch: runs created for visit', [
            'visit_id' => $visit->id,
            'count'    => $plan->checklists->count(),
        ]);
    }

    private function createInspectionFollowup(\App\Models\Work\ServicePlanVisit $visit): void
    {
        $job = $visit->serviceJob;
        if (! $job) {
            return;
        }

        // If the linked job has a return-visit outcome, create a follow-up inspection.
        if ($job->service_outcome === ServiceJob::OUTCOME_RETURN_VISIT_REQUIRED) {
            InspectionInstance::create([
                'company_id'             => $visit->company_id,
                'created_by'             => $visit->created_by,
                'scope_type'             => \App\Models\Premises\Premises::class,
                'scope_id'               => $job->premises_id,
                'service_job_id'         => $job->id,
                'title'                  => 'Follow-up inspection — ' . ($job->title ?? 'Job #' . $job->id),
                'status'                 => 'scheduled',
                'followup_required'      => true,
                'followup_notes'         => 'Auto-created after visit completion with return-visit outcome.',
            ]);

            Log::info('InspectionFollowupCreation: created', [
                'visit_id' => $visit->id,
                'job_id'   => $job->id,
            ]);
        }
    }

    private function trackAgreementUsage(\App\Models\Work\ServicePlanVisit $visit): void
    {
        $plan = $visit->plan;
        if (! $plan) {
            return;
        }

        // Advance the service plan's next_visit_due date.
        $plan->advanceNextVisitDue();

        // Record agreement-level consumption via the AgreementSchedulerService.
        $linkedJob = $visit->serviceJob;
        if ($plan->agreement_id && $plan->agreement && $linkedJob) {
            try {
                \App\Events\Work\AgreementServiceConsumed::dispatch($plan->agreement, $linkedJob);
            } catch (\Throwable $th) {
                Log::warning('AgreementUsageTracking: dispatch failed — ' . $th->getMessage());
            }
        }

        Log::info('AgreementUsageTracking: tracked', [
            'visit_id'     => $visit->id,
            'plan_id'      => $plan->id,
            'agreement_id' => $plan->agreement_id,
        ]);
    }

    private function updateAssetMaintenance(\App\Models\Work\ServicePlanVisit $visit): void
    {
        $plan = $visit->plan;
        if (! $plan?->premises_id) {
            return;
        }

        // Update last_serviced_at for all active site assets at this premises.
        $serviceDate = ($visit->completed_at ?? $visit->serviceJob?->date_end ?? now())->toDateString();

        \App\Models\Facility\SiteAsset::query()
            ->where('premises_id', $plan->premises_id)
            ->where('status', 'active')
            ->update(['last_serviced_at' => $serviceDate]);

        Log::info('AssetMaintenanceUpdate: assets updated for premises', [
            'premises_id' => $plan->premises_id,
        ]);
    }
}
