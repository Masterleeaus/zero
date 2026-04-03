<?php

declare(strict_types=1);

namespace App\Policies;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatbotPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any chatbots
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-chatbots');
    }

    /**
     * Determine if the user can view a specific chatbot
     */
    public function view(User $user, Chatbot $chatbot): bool
    {
        // User must:
        // 1. Be in the same workspace as the chatbot
        // 2. Have view-chatbots permission
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('view-chatbots');
    }

    /**
     * Determine if the user can create a chatbot
     */
    public function create(User $user): bool
    {
        return $user->can('create-chatbots');
    }

    /**
     * Determine if the user can update a chatbot
     */
    public function update(User $user, Chatbot $chatbot): bool
    {
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('manage-chatbots');
    }

    /**
     * Determine if the user can delete a chatbot
     */
    public function delete(User $user, Chatbot $chatbot): bool
    {
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('manage-chatbots');
    }

    /**
     * Determine if the user can restore a chatbot
     */
    public function restore(User $user, Chatbot $chatbot): bool
    {
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('manage-chatbots');
    }

    /**
     * Determine if the user can permanently delete a chatbot
     */
    public function forceDelete(User $user, Chatbot $chatbot): bool
    {
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('manage-chatbots');
    }
}
