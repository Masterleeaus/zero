<?php

namespace App\Extensions\Chatbot\App\Services\Queues;

class AgentExtensionBridgeService
{
    public function syncAssignment(int $conversationId, int $agentId): array
    {
        return [
            'synced' => true,
            'conversation_id' => $conversationId,
            'agent_id' => $agentId,
            'extension' => 'ChatbotAgent',
        ];
    }

    public function syncUnreadCount(int $agentId, int $count): array
    {
        return [
            'synced' => true,
            'agent_id' => $agentId,
            'unread_count' => $count,
        ];
    }
}
