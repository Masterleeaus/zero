<?php

namespace App\Http\Controllers\Omni;

use App\Http\Controllers\Controller;
use App\Models\Omni\OmniAgent;
use App\Services\Omni\OmniConversationService;
use App\Services\Omni\OmniIntelligenceDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OmniConversationController extends Controller
{
    public function store(
        Request $request,
        OmniConversationService $conversations,
        OmniIntelligenceDispatcher $dispatcher
    ): JsonResponse {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'agent_id' => ['required', 'integer'],
            'channel_type' => ['nullable', 'string'],
            'channel_id' => ['nullable', 'string'],
            'session_id' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'integer'],
            'customer_email' => ['nullable', 'email'],
            'customer_name' => ['nullable', 'string'],
            'message' => ['required', 'string'],
        ]);

        $conversation = $conversations->findOrCreate($data);

        $conversations->appendMessage($conversation, [
            'role' => 'user',
            'message_type' => $data['channel_type'] === 'voice' ? 'voice_transcript' : 'text',
            'content' => $data['message'],
        ]);

        $agent = OmniAgent::query()->findOrFail($data['agent_id']);
        $result = $dispatcher->dispatch($agent, $conversation, $data['message']);

        $conversations->appendMessage($conversation, [
            'agent_id' => $agent->id,
            'role' => 'assistant',
            'message_type' => 'text',
            'content' => $result['reply'],
            'metadata' => $result,
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'reply' => $result['reply'],
            'mode' => $result['mode'],
        ]);
    }
}
