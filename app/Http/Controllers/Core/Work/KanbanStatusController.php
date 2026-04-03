<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Controller;
use App\Models\FSM\FsmJobBlocker;
use App\Models\Work\ServiceJob;
use App\Services\FSM\KanbanStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * KanbanStatusController — REST API for kanban intelligence metadata.
 *
 * Module 23 — fieldservice_kanban_info
 *
 * Routes (all under /dashboard/work/service-jobs/{job}/):
 *   GET    kanban-state             → full kanban intelligence payload
 *   POST   kanban-state/refresh     → force re-compute and persist
 *   POST   blockers                 → add a blocking reason
 *   DELETE blockers/{blocker}       → clear (resolve) a blocking reason
 *   GET    dispatch-priority        → dispatch priority score payload
 */
class KanbanStatusController extends Controller
{
    public function __construct(private readonly KanbanStatusService $service) {}

    /**
     * Return the full kanban intelligence payload for a job.
     */
    public function show(ServiceJob $job): JsonResponse
    {
        $this->authorizeCompany($job);

        return response()->json($this->service->getJobKanbanState($job));
    }

    /**
     * Force-refresh the kanban meta and priority score for a job.
     */
    public function refresh(ServiceJob $job): JsonResponse
    {
        $this->authorizeCompany($job);

        $meta = $this->service->refresh($job);

        return response()->json([
            'message' => 'Kanban state refreshed.',
            'state'   => $this->service->getJobKanbanState($job->fresh()),
        ]);
    }

    /**
     * Add a blocking reason to a job.
     */
    public function addBlocker(Request $request, ServiceJob $job): JsonResponse
    {
        $this->authorizeCompany($job);

        $data = $request->validate([
            'blocker_type'  => ['required', 'string', 'max:100'],
            'blocker_label' => ['required', 'string', 'max:255'],
            'details'       => ['nullable', 'string', 'max:1000'],
        ]);

        $blocker = $this->service->addBlocker(
            $job,
            $data['blocker_type'],
            $data['blocker_label'],
            $data['details'] ?? null,
        );

        return response()->json([
            'message' => 'Blocker added.',
            'blocker' => [
                'id'           => $blocker->id,
                'type'         => $blocker->blocker_type,
                'label'        => $blocker->blocker_label,
                'details'      => $blocker->details,
                'is_resolved'  => $blocker->is_resolved,
            ],
        ], 201);
    }

    /**
     * Clear (resolve) a blocking reason.
     */
    public function clearBlocker(ServiceJob $job, FsmJobBlocker $blocker): JsonResponse
    {
        $this->authorizeCompany($job);

        if ($blocker->service_job_id !== $job->id) {
            abort(404);
        }

        $this->service->clearBlocker($blocker, auth()->id());

        return response()->json(['message' => 'Blocker cleared.']);
    }

    /**
     * Return the dispatch priority payload for EasyDispatch / RouteOptimizer.
     */
    public function dispatchPriority(ServiceJob $job): JsonResponse
    {
        $this->authorizeCompany($job);

        return response()->json($this->service->getDispatchPriority($job));
    }

    /**
     * Guard — ensure the job belongs to the authenticated user's company.
     */
    private function authorizeCompany(ServiceJob $job): void
    {
        $user = auth()->user();

        if (! $user || $job->company_id !== $user->company_id) {
            abort(403);
        }
    }
}
