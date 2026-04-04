<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniConversation;

class OmniIntelligenceDispatcher
{
    public function __construct(
        protected OmniKnowledgeService $knowledgeService
    ) {
    }

    public function dispatch(OmniAgent $agent, OmniConversation $conversation, string $message, array $context = []): array
    {
        $channel = $conversation->channel_type ?: 'web';

        if ($channel === 'voice') {
            return $this->dispatchVoice($agent, $conversation, $message, $context);
        }

        if ($channel === 'api') {
            return $this->dispatchApi($agent, $conversation, $message, $context);
        }

        return $this->dispatchText($agent, $conversation, $message, $context);
    }

    protected function dispatchText(OmniAgent $agent, OmniConversation $conversation, string $message, array $context = []): array
    {
        $knowledge = $this->knowledgeService->search($conversation->company_id, $agent->id, $message);

        return [
            'mode' => 'text',
            'reply' => $knowledge->isNotEmpty()
                ? 'I found relevant knowledge and can continue from the unified Omni context.'
                : 'Message routed through Omni text handler.',
            'knowledge_hits' => $knowledge->pluck('id')->all(),
            'context' => $context,
        ];
    }

    protected function dispatchVoice(OmniAgent $agent, OmniConversation $conversation, string $message, array $context = []): array
    {
        return [
            'mode' => 'voice',
            'reply' => 'Voice input routed through Omni voice handler.',
            'requires_tts' => true,
            'context' => $context,
        ];
    }

    protected function dispatchApi(OmniAgent $agent, OmniConversation $conversation, string $message, array $context = []): array
    {
        return [
            'mode' => 'api',
            'reply' => 'API request routed through Omni API handler.',
            'context' => $context,
        ];
    }
}
