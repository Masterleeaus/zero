<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mesh;

use App\Http\Controllers\Controller;
use App\Models\Mesh\MeshNode;
use App\Services\Mesh\MeshDispatchService;
use App\Services\Mesh\MeshRegistryService;
use App\Services\Mesh\MeshSignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Inbound mesh protocol endpoints — called by peer TitanZero instances.
 *
 * Security: ALL requests must carry a valid HMAC-SHA256 signature in the
 * X-Mesh-Signature header. Unsigned or invalid payloads are rejected with 401.
 */
class MeshNodeController extends Controller
{
    public function __construct(
        private readonly MeshRegistryService  $registry,
        private readonly MeshDispatchService  $dispatch,
        private readonly MeshSignatureService $signatures,
    ) {}

    // ── POST /api/mesh/handshake ─────────────────────────────────────────────

    public function handshake(Request $request): JsonResponse
    {
        $data = $request->validate([
            'node_id'    => ['required', 'uuid'],
            'node_name'  => ['required', 'string', 'max:255'],
            'node_url'   => ['required', 'url'],
            'public_key' => ['required', 'string'],
        ]);

        $node = $this->resolveAndVerifyNode($request, $data['node_id']);

        if ($node === null) {
            return $this->unauthorised('Node not found or signature invalid.');
        }

        $this->registry->performHandshake($node);

        return response()->json(['status' => 'ok', 'handshake_at' => now()->toISOString()]);
    }

    // ── POST /api/mesh/capabilities ──────────────────────────────────────────

    public function capabilities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'node_id' => ['required', 'uuid'],
        ]);

        $node = $this->resolveAndVerifyNode($request, $data['node_id']);

        if ($node === null) {
            return $this->unauthorised('Node not found or signature invalid.');
        }

        // observer and above may query capabilities
        if (! $node->meetsMinTrustLevel(MeshNode::TRUST_OBSERVER)) {
            return $this->unauthorised('Insufficient trust level.');
        }

        $exports = $this->registry->exportCapabilities($node->company_id);

        return response()->json(['data' => $exports]);
    }

    // ── POST /api/mesh/dispatch/offer ────────────────────────────────────────

    public function offer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'node_id'                  => ['required', 'uuid'],
            'mesh_dispatch_request_id' => ['required', 'integer'],
        ]);

        $node = $this->resolveAndVerifyNode($request, $data['node_id']);

        if ($node === null || ! $node->meetsMinTrustLevel(MeshNode::TRUST_STANDARD)) {
            return $this->unauthorised('Insufficient trust level for dispatch offers.');
        }

        $meshRequest = \App\Models\Mesh\MeshDispatchRequest::findOrFail($data['mesh_dispatch_request_id']);

        $this->dispatch->offerToNode($meshRequest, $node);

        return response()->json(['status' => 'offered']);
    }

    // ── POST /api/mesh/dispatch/accept ───────────────────────────────────────

    public function accept(Request $request): JsonResponse
    {
        $data = $request->validate([
            'node_id'                  => ['required', 'uuid'],
            'mesh_dispatch_request_id' => ['required', 'integer'],
        ]);

        $node = $this->resolveAndVerifyNode($request, $data['node_id']);

        if ($node === null || ! $node->meetsMinTrustLevel(MeshNode::TRUST_STANDARD)) {
            return $this->unauthorised('Insufficient trust level for dispatch acceptance.');
        }

        $meshRequest = \App\Models\Mesh\MeshDispatchRequest::findOrFail($data['mesh_dispatch_request_id']);

        $this->dispatch->acceptOffer($meshRequest);

        return response()->json(['status' => 'accepted']);
    }

    // ── POST /api/mesh/dispatch/complete ─────────────────────────────────────

    public function complete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'node_id'                  => ['required', 'uuid'],
            'mesh_dispatch_request_id' => ['required', 'integer'],
            'evidence'                 => ['required', 'array'],
        ]);

        $node = $this->resolveAndVerifyNode($request, $data['node_id']);

        if ($node === null || ! $node->meetsMinTrustLevel(MeshNode::TRUST_TRUSTED)) {
            return $this->unauthorised('Insufficient trust level for evidence submission.');
        }

        $meshRequest = \App\Models\Mesh\MeshDispatchRequest::findOrFail($data['mesh_dispatch_request_id']);

        $this->dispatch->completeRequest($meshRequest, $data['evidence']);

        return response()->json(['status' => 'completed']);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Look up the calling peer node and verify its signature.
     * Returns null if verification fails.
     */
    private function resolveAndVerifyNode(Request $request, string $nodeId): ?MeshNode
    {
        /** @var MeshNode|null $node */
        $node = MeshNode::withoutGlobalScope('company')
            ->where('node_id', $nodeId)
            ->where('is_active', true)
            ->first();

        if ($node === null) {
            Log::warning('MeshNodeController: unknown node_id', ['node_id' => $nodeId]);
            return null;
        }

        $signature = $request->header('X-Mesh-Signature', '');
        $payload   = $request->except(['_token']);

        if (! $this->signatures->verifyPayload($payload, $signature, $node)) {
            Log::warning('MeshNodeController: signature verification failed', ['node_id' => $nodeId]);
            return null;
        }

        return $node;
    }

    private function unauthorised(string $message): JsonResponse
    {
        return response()->json(['error' => $message], 401);
    }
}
