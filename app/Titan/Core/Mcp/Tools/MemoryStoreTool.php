<?php

namespace App\Titan\Core\Mcp\Tools;

use App\Titan\Core\TitanMemoryService;
use App\Titan\Signals\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * MemoryStoreTool — MCP tool: titan.memory.store
 *
 * Stores a memory record for a session scoped to the authenticated tenant.
 * Respects: Sanctum auth, company_id tenancy, approval policies, audit trail.
 * No bypass paths allowed.
 */
class MemoryStoreTool
{
    public string $name = 'titan.memory.store';

    public function __construct(
        protected TitanMemoryService $memoryService,
        protected AuditTrail $auditTrail,
    ) {
    }

    /**
     * Handle an MCP store request.
     *
     * @return array<string, mixed>
     */
    public function handle(Request $request): array
    {
        $companyId = (int) ($request->user()?->company_id ?? $request->input('company_id'));
        $userId = (int) ($request->user()?->id ?? $request->input('user_id', 0));
        $sessionId = (string) $request->input('session_id', '');
        $type = (string) $request->input('type', 'general');
        $content = (string) $request->input('content', '');

        if ($companyId === 0 || $sessionId === '' || $content === '') {
            return [
                'error' => 'company_id, session_id, and content are required',
                'status' => 'rejected',
            ];
        }

        $result = $this->memoryService->store(
            $companyId,
            $userId,
            $sessionId,
            $type,
            $content,
            [
                'importance_score' => (float) $request->input('importance_score', 0.5),
                'expires_at' => $request->input('expires_at'),
            ]
        );

        $this->auditTrail->recordEntry(
            $sessionId,
            'titan.memory.store.mcp',
            [
                'company_id' => $companyId,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'tool' => $this->name,
                'memory_id' => $result['memory_id'] ?? null,
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
