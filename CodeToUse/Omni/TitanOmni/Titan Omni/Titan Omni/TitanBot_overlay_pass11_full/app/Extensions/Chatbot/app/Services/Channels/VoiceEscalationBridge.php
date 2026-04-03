<?php

namespace App\Extensions\Chatbot\App\Services\Channels;

class VoiceEscalationBridge
{
    public function escalate(int $conversationId, int $agentId): array
    {
        return [
            'voice_escalated' => true,
            'conversation_id' => $conversationId,
            'agent_id' => $agentId,
            'target' => 'ChatbotAgent',
        ];
    }
}
