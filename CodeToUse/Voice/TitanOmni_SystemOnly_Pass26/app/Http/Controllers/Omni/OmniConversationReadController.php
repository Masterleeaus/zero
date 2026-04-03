<?php

namespace App\Http\Controllers\Omni;

use App\Http\Controllers\Controller;
use App\Services\Omni\OmniConversationReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OmniConversationReadController extends Controller
{
    public function index(Request $request, OmniConversationReadService $reads): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'agent_id' => ['required', 'integer'],
            'limit' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'items' => $reads->recentForAgent($data['company_id'], $data['agent_id'], $data['limit'] ?? 25),
        ]);
    }
}
