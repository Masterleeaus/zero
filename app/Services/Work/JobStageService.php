<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\JobCompleted;
use App\Events\Work\JobReadyForInvoice;
use App\Events\Work\JobStageChanged;
use App\Events\Work\JobStarted;
use App\Models\Work\JobStage;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * JobStageService
 *
 * Handles all field-service job stage lifecycle transitions with:
 *  - Validation rules (signature enforcement, closed-stage guards)
 *  - Automated status synchronization (scheduled / in_progress / completed)
 *  - Signal emission for downstream automation
 */
class JobStageService
{
    /**
     * Move a job to a new stage.
     *
     * Validates all transition rules before persisting the change and
     * emits the appropriate lifecycle events.
     *
     * @throws ValidationException
     */
    public function transition(ServiceJob $job, JobStage $newStage): ServiceJob
    {
        $this->validateTransition($job, $newStage);

        return DB::transaction(function () use ($job, $newStage) {
            $previousStage = $job->stage;

            $job->stage_id = $newStage->id;

            // Synchronize the flat status field with stage meta
            $job->status = $this->resolveStatus($newStage);

            $job->save();

            // Reload the stage relation
            $job->setRelation('stage', $newStage);

            // Core stage-change signal
            JobStageChanged::dispatch($job, $previousStage, $newStage);

            // Targeted lifecycle signals
            if ($job->status === 'in_progress' && ($previousStage?->stage_type !== 'order' || ! $previousStage?->is_closed)) {
                JobStarted::dispatch($job);
            }

            if ($newStage->is_closed) {
                JobCompleted::dispatch($job);
            }

            // Billing signal: stage is marked as invoiceable
            if ($newStage->is_invoiceable) {
                JobReadyForInvoice::dispatch($job);
            }

            // Time graph integration — record stage transition event
            try {
                $graphService = app(\App\Services\TimeGraph\ExecutionTimeGraphService::class);
                $graph = \App\Models\TimeGraph\ExecutionGraph::query()
                    ->withoutGlobalScope('company')
                    ->where('root_subject_type', get_class($job))
                    ->where('root_subject_id', $job->getKey())
                    ->where('status', 'active')
                    ->first();

                if ($graph) {
                    $graphService->record(
                        graphId: $graph->graph_id,
                        eventClass: JobStageChanged::class,
                        subject: $job,
                        payload: [
                            'from_stage'    => $previousStage?->name,
                            'to_stage'      => $newStage->name,
                            'from_stage_id' => $previousStage?->id,
                            'to_stage_id'   => $newStage->id,
                        ],
                        eventType: 'stage_transition',
                        actorType: auth()->check() ? 'user' : 'system',
                        actorId: auth()->id(),
                    );
                }
            } catch (\Throwable $e) {
                // Time graph recording must not disrupt the stage transition
                logger()->warning('ExecutionTimeGraph: failed to record stage transition', ['error' => $e->getMessage()]);
            }

            return $job;
        });
    }

    /**
     * Validate all pre-conditions for a stage transition.
     *
     * @throws ValidationException
     */
    public function validateTransition(ServiceJob $job, JobStage $newStage): void
    {
        // Cannot move out of a closed stage unless explicitly allowed
        if ($job->stage && $job->stage->is_closed) {
            throw ValidationException::withMessages([
                'stage_id' => __('This job is closed and cannot be moved to another stage.'),
            ]);
        }

        // Signature required before moving to a stage that demands it
        if ($newStage->require_signature && ! $job->signed_on) {
            throw ValidationException::withMessages([
                'signed_on' => __('A customer signature is required before advancing to the ":stage" stage.', [
                    'stage' => $newStage->name,
                ]),
            ]);
        }

        // No-op move guard
        if ($job->stage_id === $newStage->id) {
            throw ValidationException::withMessages([
                'stage_id' => __('The job is already in the ":stage" stage.', [
                    'stage' => $newStage->name,
                ]),
            ]);
        }

        // Block closure when required activities are still pending (Module 4)
        if ($newStage->is_closed && ! $job->hasRequiredActivitiesDone()) {
            throw ValidationException::withMessages([
                'activities' => __('All required activities must be completed before closing this job.'),
            ]);
        }
    }

    /**
     * Resolve the flat status string from stage meta.
     */
    private function resolveStatus(JobStage $stage): string
    {
        if ($stage->is_closed) {
            return 'completed';
        }

        // The default/first stage implies scheduled; any later stage means work has started
        if ($stage->is_default) {
            return 'scheduled';
        }

        return 'in_progress';
    }

    /**
     * Retrieve the ordered pipeline stages for orders, scoped to company.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, JobStage>
     */
    public function orderPipeline(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return JobStage::query()
            ->where('company_id', $companyId)
            ->forOrders()
            ->get();
    }

    /**
     * Return the default (entry) stage for the given company, or null if none configured.
     */
    public function defaultStage(int $companyId): ?JobStage
    {
        return JobStage::query()
            ->where('company_id', $companyId)
            ->default()
            ->forOrders()
            ->first();
    }
}
