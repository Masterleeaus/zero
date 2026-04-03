<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\ChecklistRunCompleted;
use App\Models\Inspection\InspectionInstance;
use App\Models\Premises\Hazard;
use App\Models\Work\ChecklistRun;
use App\Models\Work\ServiceJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to ChecklistRunCompleted events.
 *
 * Responsibilities:
 *   1. Create a follow-up InspectionInstance if the checklist required one.
 *   2. Create a Hazard record if any response flagged a hazard.
 *   3. Log a compliance record for the completed run.
 */
class ChecklistRunCompletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(ChecklistRunCompleted $event): void
    {
        $run = $event->checklistRun;

        try {
            $this->createInspectionIfRequired($run);
            $this->createHazardIfDetected($run);
            $this->updateComplianceLog($run);
        } catch (\Throwable $th) {
            Log::error('ChecklistRunCompletedListener: ' . $th->getMessage(), [
                'checklist_run_id' => $run->id,
            ]);
        }
    }

    private function createInspectionIfRequired(ChecklistRun $run): void
    {
        // Create a follow-up inspection when the checklist run has failed items.
        if (! $run->hasFailed()) {
            return;
        }

        $premisesId = $this->resolvePremisesId($run);
        if (! $premisesId) {
            return;
        }

        InspectionInstance::create([
            'company_id'        => $run->company_id,
            'created_by'        => $run->created_by,
            'scope_type'        => \App\Models\Premises\Premises::class,
            'scope_id'          => $premisesId,
            'title'             => 'Follow-up inspection — ' . ($run->title ?? 'Checklist #' . $run->id),
            'status'            => 'scheduled',
            'followup_required' => true,
            'followup_notes'    => 'Auto-created after checklist run with failed items.',
        ]);

        Log::info('InspectionCreationIfRequired: created', ['checklist_run_id' => $run->id]);
    }

    private function createHazardIfDetected(ChecklistRun $run): void
    {
        // Check if there are any failed responses with notes (potential hazard flags).
        // Extension point: add a flags_hazard column to checklist_responses to enable
        // explicit hazard tagging; for now, failed runs with notes trigger review.
        if (! $run->hasFailed()) {
            return;
        }

        $failedWithNotes = $run->responses()
            ->where('result', 'fail')
            ->whereNotNull('notes')
            ->first();

        if (! $failedWithNotes) {
            return;
        }

        $premisesId = $this->resolvePremisesId($run);
        if (! $premisesId) {
            return;
        }

        $hazard = Hazard::create([
            'company_id'    => $run->company_id,
            'created_by'    => $run->created_by,
            'premises_id'   => $premisesId,
            'title'         => 'Hazard detected during checklist — ' . ($run->title ?? '#' . $run->id),
            'description'   => $failedWithNotes->notes,
            'severity'      => 'medium',
            'status'        => 'active',
            'identified_at' => ($run->completed_at ?? now())->toDateString(),
        ]);

        \App\Events\Premises\HazardDetected::dispatch($hazard);

        Log::info('HazardCreationIfDetected: hazard created', [
            'checklist_run_id' => $run->id,
            'hazard_id'        => $hazard->id,
        ]);
    }

    private function updateComplianceLog(ChecklistRun $run): void
    {
        // Extension point: push a compliance record to a compliance log table or external system.
        Log::info('ComplianceLogUpdate: checklist run recorded', [
            'checklist_run_id' => $run->id,
            'status'           => $run->status,
            'pass_rate'        => $run->passRate(),
        ]);
    }

    private function resolvePremisesId(ChecklistRun $run): ?int
    {
        $runnable = $run->runnable;

        if ($runnable instanceof \App\Models\Premises\Premises) {
            return $runnable->id;
        }

        if ($runnable instanceof ServiceJob) {
            return $runnable->premises_id;
        }

        if ($runnable instanceof \App\Models\Work\ServicePlanVisit) {
            return $runnable->plan?->premises_id;
        }

        if ($runnable instanceof InspectionInstance) {
            return ($runnable->scope_type === \App\Models\Premises\Premises::class)
                ? $runnable->scope_id
                : null;
        }

        return null;
    }
}
