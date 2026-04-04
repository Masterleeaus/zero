<?php

declare(strict_types=1);

namespace App\Http\Controllers\TimeGraph;

use App\Http\Controllers\Controller;
use App\Models\TimeGraph\ExecutionGraph;
use App\Services\TimeGraph\ExecutionReplayService;
use App\Services\TimeGraph\ExecutionTimeGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExecutionTimeGraphController extends Controller
{
    public function __construct(
        private readonly ExecutionTimeGraphService $graphService,
        private readonly ExecutionReplayService $replayService,
    ) {}

    public function timeline(Request $request, string $graphId): JsonResponse
    {
        $graph = ExecutionGraph::query()->withoutGlobalScope('company')->where('graph_id', $graphId)->firstOrFail();
        $events = $this->graphService->getTimeline($graphId);

        return response()->json([
            'graph'  => $graph,
            'events' => $events,
        ]);
    }

    public function graph(string $graphId): JsonResponse
    {
        $graph = ExecutionGraph::query()
            ->withoutGlobalScope('company')
            ->with(['events', 'checkpoints'])
            ->where('graph_id', $graphId)
            ->firstOrFail();

        return response()->json($graph);
    }

    public function checkpoint(Request $request, string $graphId): JsonResponse
    {
        $request->validate(['label' => 'required|string|max:255']);

        $checkpoint = $this->graphService->createCheckpoint($graphId, $request->string('label')->toString());

        return response()->json($checkpoint, 201);
    }

    public function replay(Request $request, string $graphId): JsonResponse
    {
        $request->validate(['to_time' => 'required|date']);

        $graph = ExecutionGraph::query()->withoutGlobalScope('company')->where('graph_id', $graphId)->firstOrFail();
        $plan  = $this->replayService->buildReplayPlan($graph, now()->parse($request->input('to_time')));

        return response()->json($plan);
    }

    public function describe(string $graphId): JsonResponse
    {
        $graph    = ExecutionGraph::query()->withoutGlobalScope('company')->where('graph_id', $graphId)->firstOrFail();
        $exported = $this->replayService->exportTimeline($graph);

        return response()->json($exported);
    }
}
