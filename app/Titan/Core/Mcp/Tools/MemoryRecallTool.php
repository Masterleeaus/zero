<?php

namespace App\Titan\Core\Mcp\Tools;

use App\Titan\Core\TitanMemoryService;
use App\Titan\Signals\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * MemoryRecallTool — MCP tool: titan.memory.recall
 *
 * Recalls memories for a session scoped to the authenticated tenant.
 * Respects: Sanctum auth, company_id tenancy, approval policies, audit trail.
 */
class MemoryRecallTool
{
    public string $name = 'titan.memory.recall';

    public function __construct(
        protected TitanMemoryService $memoryService,
        protected AuditTrail $auditTrail,
    ) {
    }

    /**
     * Handle an MCP recall request.
     *
     * @return array<string, mixed>
     */
    public function handle(Request $request): array
    {
        $companyId = (int) ($request->user()?->company_id ?? $request->input('company_id'));
        $sessionId = (string) $request->input('session_id', '');
        $query = $request->input('query');
        $type = $request->input('type');
        $limit = (int) $request->input('limit', 20);

        if ($companyId === 0 || $sessionId === '') {
            return [
                'error' => 'company_id and session_id are required',
                'status' => 'rejected',
            ];
        }

        $result = $this->memoryService->recall($companyId, $sessionId, array_filter([
            'query' => $query,
            'type' => $type,
            'limit' => $limit,
        ]));

        $this->auditTrail->recordEntry(
            $sessionId,
            'titan.memory.recall.mcp',
            [
                'company_id' => $companyId,
                'user_id' => $request->user()?->id,
                'session_id' => $sessionId,
                'tool' => $this->name,
            ]
        );

        return array_merge($result, [
            'tool' => $this->name,
            'status' => 'ok',
        ]);
    }

    /**
     * Handle an HTTP JSON request (for route binding).
     */
    public function respond(Request $request): JsonResponse
    {
        return response()->json($this->handle($request));
    }
}
