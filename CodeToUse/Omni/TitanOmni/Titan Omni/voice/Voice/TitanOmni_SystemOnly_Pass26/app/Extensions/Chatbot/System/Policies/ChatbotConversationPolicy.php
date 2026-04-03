<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Policies;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Models\User;

class ChatbotConversationPolicy
{
    public function view(User $user, ChatbotConversation $conversation): bool
    {
        return $this->ownsWorkspace($user, $conversation) || $conversation->assigned_agent_id === $user->id;
    }

    public function respond(User $user, ChatbotConversation $conversation): bool
    {
        return $this->ownsWorkspace($user, $conversation) || $conversation->assigned_agent_id === $user->id;
    }

    public function transfer(User $user, ChatbotConversation $conversation): bool
    {
        return $this->respond($user, $conversation);
    }

    public function close(User $user, ChatbotConversation $conversation): bool
    {
        return $this->respond($user, $conversation);
    }

    protected function ownsWorkspace(User $user, ChatbotConversation $conversation): bool
    {
        $workspaceId = data_get($user, 'workspace.id') ?? data_get($user, 'company_id') ?? data_get($user, 'team_id');

        if ($conversation->chatbot && $workspaceId) {
            return in_array($workspaceId, array_filter([
                $conversation->chatbot->workspace_id,
                $conversation->chatbot->team_id,
                $conversation->chatbot->company_id,
            ]), true);
        }

        return (int) $conversation->chatbot?->user_id === (int) $user->id;
    }
}
