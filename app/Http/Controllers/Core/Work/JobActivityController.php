<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\JobActivity;
use App\Models\Work\JobTemplate;
use App\Models\Work\ServiceJob;
use App\Services\Work\JobActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * JobActivityController
 *
 * Handles HTTP lifecycle actions for job activities (module 4).
 *
 * All mutating actions are POST/PUT/DELETE; no GET routes are needed here
 * because activities are rendered inline on the service-job or job-template
 * show pages.  Controllers stay thin — all business logic is in
 * JobActivityService.
 */
class JobActivityController extends CoreController
{
    public function __construct(private readonly JobActivityService $service) {}

    // ── Live activities on service jobs ──────────────────────────────────────

    /**
     * Create an ad-hoc activity on a service job.
     */
    public function store(Request $request, ServiceJob $job): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'ref'         => ['nullable', 'string', 'max:100'],
            'sequence'    => ['nullable', 'integer', 'min:0'],
            'required'    => ['nullable', 'boolean'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'team_id'     => ['nullable', 'integer', 'exists:teams,id'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        $this->service->createForJob($job, $data);

        return back()->with([
            'type'    => 'success',
            'message' => __('Activity added.'),
        ]);
    }

    /**
     * Update a live activity (name, ref, sequence, required, assignment).
     */
    public function update(Request $request, ServiceJob $job, JobActivity $jobActivity): RedirectResponse
    {
        abort_unless((int) $jobActivity->service_job_id === $job->id, 403);

        $data = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'ref'         => ['nullable', 'string', 'max:100'],
            'sequence'    => ['nullable', 'integer', 'min:0'],
            'required'    => ['nullable', 'boolean'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'team_id'     => ['nullable', 'integer', 'exists:teams,id'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        $jobActivity->update($data);

        return back()->with([
            'type'    => 'success',
            'message' => __('Activity updated.'),
        ]);
    }

    /**
     * Delete a live activity from a service job.
     */
    public function destroy(ServiceJob $job, JobActivity $jobActivity): RedirectResponse
    {
        abort_unless((int) $jobActivity->service_job_id === $job->id, 403);

        $jobActivity->delete();

        return back()->with([
            'type'    => 'success',
            'message' => __('Activity removed.'),
        ]);
    }

    /**
     * Mark an activity as completed.
     */
    public function complete(ServiceJob $job, JobActivity $jobActivity): RedirectResponse
    {
        abort_unless((int) $jobActivity->service_job_id === $job->id, 403);

        $this->service->complete($jobActivity, auth()->user());

        return back()->with([
            'type'    => 'success',
            'message' => __('Activity completed.'),
        ]);
    }

    /**
     * Dismiss (cancel) an activity.
     */
    public function dismiss(ServiceJob $job, JobActivity $jobActivity): RedirectResponse
    {
        abort_unless((int) $jobActivity->service_job_id === $job->id, 403);

        $this->service->dismiss($jobActivity);

        return back()->with([
            'type'    => 'success',
            'message' => __('Activity dismissed.'),
        ]);
    }

    /**
     * Schedule a follow-up date for an activity.
     */
    public function scheduleFollowUp(Request $request, ServiceJob $job, JobActivity $jobActivity): RedirectResponse
    {
        abort_unless((int) $jobActivity->service_job_id === $job->id, 403);

        $data = $request->validate([
            'follow_up_at' => ['required', 'date'],
        ]);

        $this->service->scheduleFollowUp($jobActivity, \Carbon\Carbon::parse($data['follow_up_at']));

        return back()->with([
            'type'    => 'success',
            'message' => __('Follow-up scheduled.'),
        ]);
    }

    /**
     * Bulk reorder activities on a service job.
     *
     * Expects JSON: { "ids": [3, 1, 2] } where the array order defines the new sequence.
     */
    public function reorder(Request $request, ServiceJob $job): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->service->reorder($job, $data['ids']);

        return back()->with([
            'type'    => 'success',
            'message' => __('Activities reordered.'),
        ]);
    }

    // ── Template activity definitions ─────────────────────────────────────────

    /**
     * Add an activity definition to a job template.
     */
    public function storeForTemplate(Request $request, JobTemplate $jobTemplate): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'ref'      => ['nullable', 'string', 'max:100'],
            'sequence' => ['nullable', 'integer', 'min:0'],
            'required' => ['nullable', 'boolean'],
        ]);

        $this->service->createForTemplate($jobTemplate, $data);

        return back()->with([
            'type'    => 'success',
            'message' => __('Activity definition added to template.'),
        ]);
    }

    /**
     * Remove an activity definition from a job template.
     */
    public function destroyTemplateActivity(JobTemplate $jobTemplate, JobActivity $jobActivity): RedirectResponse
    {
        abort_unless((int) $jobActivity->template_id === $jobTemplate->id, 403);

        $jobActivity->delete();

        return back()->with([
            'type'    => 'success',
            'message' => __('Activity definition removed.'),
        ]);
    }
}
