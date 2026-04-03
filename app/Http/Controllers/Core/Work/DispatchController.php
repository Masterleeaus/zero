<?php

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Controller;
use App\Models\Work\DispatchAssignment;
use App\Models\Work\DispatchQueue;
use App\Models\Work\ServiceJob;
use App\Services\Work\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    public function __construct(protected DispatchService $dispatchService) {}

    public function index(): JsonResponse
    {
        $queue = DispatchQueue::with('job')
            ->orderByDesc('priority_score')
            ->paginate(25);

        return response()->json($queue);
    }

    public function assign(Request $request): JsonResponse
    {
        $request->validate([
            'job_id'        => ['required', 'integer', 'exists:service_jobs,id'],
            'technician_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $job = ServiceJob::findOrFail($request->job_id);
        $assignment = $this->dispatchService->manualAssign($job, (int) $request->technician_id);

        return response()->json([
            'status'     => 'assigned',
            'assignment' => $assignment,
        ], 201);
    }

    public function autoDispatch(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => ['required', 'integer', 'exists:service_jobs,id'],
        ]);

        $job = ServiceJob::findOrFail($request->job_id);
        // Queue for tracking, then allocate immediately
        $this->dispatchService->queueForDispatch($job);
        $assignment = $this->dispatchService->allocate($job);

        return response()->json([
            'status'     => 'auto-dispatched',
            'assignment' => $assignment,
        ], 201);
    }

    public function history(Request $request): JsonResponse
    {
        $jobId = $request->query('job_id');

        $query = DispatchAssignment::with(['job', 'technician'])
            ->orderByDesc('assigned_at');

        if ($jobId) {
            $query->where('job_id', $jobId);
        }

        return response()->json($query->paginate(25));
    }
}
