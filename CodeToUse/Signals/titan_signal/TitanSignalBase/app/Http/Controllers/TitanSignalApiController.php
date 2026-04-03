<?php

namespace App\Http\Controllers;

use App\Titan\Signals\ProcessRecorder;
use App\Titan\Signals\ProcessStateMachine;
use App\Titan\Signals\SignalsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitanSignalApiController extends Controller
{
    public function __construct(
        protected SignalsService $signals,
        protected ProcessRecorder $processes,
        protected ProcessStateMachine $states,
    ) {
    }

    protected function assertCompanyScope(Request $request, int $companyId): void
    {
        if (auth()->check() && (int) (auth()->user()->company_id ?? 0) !== $companyId) {
            abort(403, 'Cross-company signal access denied.');
        }
    }

    public function ingest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'team_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'process_id' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'kind' => ['nullable', 'string'],
            'severity' => ['nullable', 'string'],
            'payload' => ['nullable', 'array'],
            'meta' => ['nullable', 'array'],
        ]);
        $this->assertCompanyScope($request, (int) $data['company_id']);

        return response()->json(['ok' => true, 'signal' => $this->signals->ingest($data)]);
    }

    public function publish(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'signals' => ['required', 'array'],
        ]);
        $this->assertCompanyScope($request, (int) $data['company_id']);

        $signals = array_map(function (array $signal) use ($data) {
            $signal['company_id'] = $signal['company_id'] ?? $data['company_id'];
            return $signal;
        }, $data['signals']);

        return response()->json(['ok' => true, 'signals' => $this->signals->publish($signals)]);
    }

    public function recordProcess(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'team_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'entity_type' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'data' => ['nullable', 'array'],
            'context' => ['nullable', 'array'],
            'originating_node' => ['nullable', 'string'],
        ]);
        $this->assertCompanyScope($request, (int) $data['company_id']);

        return response()->json(['ok' => true, 'process' => $this->processes->record($data)]);
    }

    public function recordAndIngest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'team_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'entity_type' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'data' => ['nullable', 'array'],
            'context' => ['nullable', 'array'],
            'originating_node' => ['nullable', 'string'],
            'signal' => ['nullable', 'array'],
        ]);
        $this->assertCompanyScope($request, (int) $data['company_id']);

        return response()->json([
            'ok' => true,
            'result' => $this->signals->recordAndIngest($data, $data['signal'] ?? []),
        ]);
    }

    public function transitionProcess(Request $request, string $processId): JsonResponse
    {
        $data = $request->validate([
            'to_state' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        return response()->json(['ok' => true, 'transition' => $this->states->transitionState($processId, $data['to_state'], $data['metadata'] ?? [])]);
    }

    public function dispatchPending(Request $request): JsonResponse
    {
        $data = $request->validate([
            'limit' => ['nullable', 'integer'],
        ]);

        return response()->json(['ok' => true, 'dispatch' => $this->signals->dispatchPending((int) ($data['limit'] ?? 50))]);
    }

    public function approvals(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'team_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'current_approver' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer'],
        ]);
        $this->assertCompanyScope($request, (int) $data['company_id']);

        return response()->json(['ok' => true, 'approvals' => $this->signals->approvals((int) $data['company_id'], $data)]);
    }

    public function approve(Request $request, string $processId): JsonResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'string'],
            'actor' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        return response()->json(['ok' => true, 'approval' => $this->signals->approvalDecision($processId, $data['decision'], $data['actor'] ?? null, $data['meta'] ?? [])]);
    }

    public function feed(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'team_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'process_id' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'severity' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer'],
            'ranked' => ['nullable', 'boolean'],
        ]);
        $this->assertCompanyScope($request, (int) $data['company_id']);

        return response()->json(['ok' => true, 'signals' => $this->signals->feed((int) $data['company_id'], $data)]);
    }

    public function envelope(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'team_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
        ]);
        $this->assertCompanyScope($request, (int) $data['company_id']);

        return response()->json($this->signals->envelope((int) $data['company_id'], $data['team_id'] ?? null, $data['user_id'] ?? null));
    }

    public function timeline(string $processId): JsonResponse
    {
        return response()->json(['ok' => true, 'timeline' => $this->signals->timeline($processId)]);
    }

    public function process(string $processId): JsonResponse
    {
        return response()->json(['ok' => true, 'process' => $this->signals->process($processId)]);
    }

    public function registry(): JsonResponse
    {
        return response()->json(['ok' => true, 'registry' => $this->signals->registry()]);
    }
}
