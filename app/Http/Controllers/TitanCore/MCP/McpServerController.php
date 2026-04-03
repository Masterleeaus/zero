<?php

namespace App\Http\Controllers\TitanCore\MCP;

use App\Http\Controllers\Controller;
use App\TitanCore\MCP\McpCapabilityRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * McpServerController — HTTP transport for MCP capability invocation.
 *
 * All routes require auth:sanctum + company tenancy enforcement.
 * Approval gating is delegated to the underlying capability handler.
 */
class McpServerController extends Controller
{
    public function __construct(
        protected McpCapabilityRegistry $registry,
    ) {
    }

    /**
     * GET /api/titan/mcp/capabilities
     * List all registered MCP capabilities.
     */
    public function capabilities(): JsonResponse
    {
        return response()->json([
            'ok'           => true,
            'capabilities' => array_values(array_map(
                fn (array $c) => [
                    'name'           => $c['name'],
                    'description'    => $c['description'],
                    'auth'           => $c['auth'],
                    'tenancy'        => $c['tenancy'],
                    'approval_aware' => $c['approval_aware'],
                    'rate_limit'     => $c['rate_limit'],
                ],
                $this->registry->all(),
            )),
        ]);
    }

    /**
     * POST /api/titan/mcp/invoke
     * Invoke an MCP capability by name.
     */
    public function invoke(Request $request): JsonResponse
    {
        $name   = (string) $request->input('capability', '');
        $params = (array)  $request->input('params', []);

        $capability = $this->registry->get($name);

        if ($capability === null) {
            return response()->json(['ok' => false, 'error' => "Unknown capability: {$name}"], 404);
        }

        $companyId = (int) ($request->user()?->company_id ?? 0);

        if ($capability['tenancy'] && $companyId === 0) {
            return response()->json(['ok' => false, 'error' => 'Tenancy required: company_id missing'], 403);
        }

        $params['company_id'] = $companyId;
        $params['user_id']    = $request->user()?->id;

        try {
            $handler = app($capability['handler']);
            $result  = $handler->handle($params);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }

        return response()->json(array_merge(['ok' => true, 'capability' => $name], $result));
    }

    /**
     * POST /api/titan/signal/callback
     * Signed callback endpoint for Zylos skill runtime.
     * Signature validation is enforced by ValidateZylosSignature middleware.
     */
    public function skillCallback(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        return response()->json([
            'ok'          => true,
            'received_at' => now()->toIso8601String(),
            'payload'     => $payload,
        ]);
    }
}
