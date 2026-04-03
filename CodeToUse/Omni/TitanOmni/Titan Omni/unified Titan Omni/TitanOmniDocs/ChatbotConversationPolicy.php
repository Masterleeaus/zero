<?php

declare(strict_types=1);

namespace App\Policies;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatbotConversationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can respond to a conversation (Agent)
     */
    public function respondAs(User $user, ChatbotConversation $conversation): bool
    {
        // User can respond if:
        // 1. They are assigned to this conversation, OR
        // 2. They have manage-all-conversations permission

        $isAssigned = $conversation->assigned_agent_id === $user->id;
        $isAdmin = $user->can('manage-all-conversations');

        return $isAssigned || $isAdmin;
    }

    /**
     * Determine if the user can view a conversation
     */
    public function view(User $user, ChatbotConversation $conversation): bool
    {
        // User can view if:
        // 1. They are the customer (chatbot_customer_id), OR
        // 2. They are the assigned agent, OR
        // 3. They have manage-all-conversations permission, OR
        // 4. They are owner of the chatbot and have manage-chatbots permission

        $isCustomer = $user->id === $conversation->chatbot_customer_id;
        $isAgent = $user->id === $conversation->assigned_agent_id;
        $isAdmin = $user->can('manage-all-conversations');
        $isOwner = $conversation->chatbot->workspace_id === $user->workspace_id
            && $user->can('manage-chatbots');

        return $isCustomer || $isAgent || $isAdmin || $isOwner;
    }

    /**
     * Determine if the user can transfer a conversation (Agent)
     */
    public function transfer(User $user, ChatbotConversation $conversation): bool
    {
        // User can transfer if they own the conversation or have admin rights
        return $user->id === $conversation->assigned_agent_id
            || $user->can('manage-all-conversations');
    }

    /**
     * Determine if the user can close a conversation (Agent/Owner)
     */
    public function close(User $user, ChatbotConversation $conversation): bool
    {
        // User can close if:
        // 1. They are the assigned agent, OR
        // 2. They are the chatbot owner, OR
        // 3. They have manage-all-conversations permission

        $isAgent = $user->id === $conversation->assigned_agent_id;
        $isOwner = $conversation->chatbot->workspace_id === $user->workspace_id
            && $user->can('manage-chatbots');
        $isAdmin = $user->can('manage-all-conversations');

        return $isAgent || $isOwner || $isAdmin;
    }

    /**
     * Determine if the user can reopen a closed conversation (Customer)
     */
    public function reopen(User $user, ChatbotConversation $conversation): bool
    {
        // Only the customer can reopen their own conversation
        return $user->id === $conversation->chatbot_customer_id;
    }

    /**
     * Determine if the user can rate a conversation (Customer)
     */
    public function rate(User $user, ChatbotConversation $conversation): bool
    {
        // Only the customer can rate their own conversation
        return $user->id === $conversation->chatbot_customer_id;
    }

    /**
     * Determine if the user can export a conversation
     */
    public function export(User $user, ChatbotConversation $conversation): bool
    {
        // User can export if they can view the conversation
        return $this->view($user, $conversation);
    }
}
