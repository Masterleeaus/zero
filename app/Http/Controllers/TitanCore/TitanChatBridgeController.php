<?php

namespace App\Http\Controllers\TitanCore;

use App\Http\Controllers\Controller;
use App\Services\TitanChat\TitanChatBridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * TitanChatBridgeController
 *
 * REST API controller exposing the canonical chat bridge for:
 *   - AIChatPro UI
 *   - Canvas UI
 *   - Generic chat surfaces
 *
 * All routes require authentication. Execution routes through:
 *   OmniManager → TitanAIRouter → TitanMemory → Signal/Approval/Rewind
 */
class TitanChatBridgeController extends Controller
{
    public function __construct(
        protected TitanChatBridge $bridge,
    ) {
    }

    /**
     * POST /api/titan/chat/send
     *
     * Execute a chat turn from any workspace surface (AIChatPro, Canvas, generic).
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input'       => 'required|string|max:32768',
            'surface'     => 'sometimes|string|in:aichatpro,canvas,chatbot,workspace',
            'session_id'  => 'sometimes|string|max:255',
            'thread_id'   => 'sometimes|nullable|string|max:255',
            'category_id' => 'sometimes|nullable|integer',
            'model'       => 'sometimes|nullable|string|max:255',
            'intent'      => 'sometimes|string|max:100',
            'memory_refs' => 'sometimes|array',
            'attachments' => 'sometimes|array',
        ]);

        $user    = Auth::user();
        $surface = $validated['surface'] ?? 'workspace';

        $envelope = match ($surface) {
            'canvas'    => $this->bridge->buildCanvasEnvelope([
                ...$validated,
                'company_id' => $user?->company_id,
                'user_id'    => $user?->id,
            ]),
            'aichatpro' => $this->bridge->buildChatProEnvelope([
                ...$validated,
                'company_id' => $user?->company_id,
                'user_id'    => $user?->id,
            ]),
            default     => [
                ...$validated,
                'surface'    => $surface,
                'channel'    => 'workspace',
                'company_id' => $user?->company_id,
                'user_id'    => $user?->id,
            ],
        };

        $result = $this->bridge->chat($envelope);

        return response()->json($result);
    }

    /**
     * GET /api/titan/chat/status
     *
     * Return the bridge/router health status.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'bridge'   => 'TitanChatBridge',
            'channels' => $this->bridge->registeredChannels(),
            'pipeline' => [
                'omni'   => \App\TitanCore\Omni\OmniManager::class,
                'router' => \App\TitanCore\Zero\AI\TitanAIRouter::class,
                'memory' => \App\Titan\Core\TitanMemoryService::class,
            ],
        ]);
    }
}
