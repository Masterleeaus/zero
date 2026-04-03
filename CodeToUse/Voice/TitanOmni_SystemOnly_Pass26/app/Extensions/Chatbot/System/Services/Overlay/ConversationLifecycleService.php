<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Overlay;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ConversationLifecycleService
{
    public function claim(ChatbotConversation $conversation, int $agentId): ChatbotConversation
    {
        $conversation->forceFill([
            'assigned_agent_id' => $agentId,
            'connect_agent_at' => $conversation->connect_agent_at ?? now(),
            'last_activity_at' => now(),
            'closed' => false,
            'closed_at' => null,
        ])->save();

        return $conversation->fresh(['assignedAgent']);
    }

    public function reply(ChatbotConversation $conversation, int $agentId, string $message, ?UploadedFile $attachment = null): ChatbotHistory
    {
        return DB::transaction(function () use ($conversation, $agentId, $message, $attachment) {
            $attachmentPath = $attachment?->store('chatbot-attachments', 'public');

            $history = ChatbotHistory::query()->create([
                'user_id' => $agentId,
                'chatbot_id' => $conversation->chatbot_id,
                'conversation_id' => $conversation->id,
                'message' => $message,
                'media_name' => $attachmentPath,
                'role' => 'assistant',
                'model' => 'human-agent',
                'message_type' => $attachmentPath ? 'file' : 'text',
                'created_at' => now(),
                'team_id' => $conversation->team_id,
                'company_id' => $conversation->company_id,
            ]);

            $this->claim($conversation, $agentId);

            return $history;
        });
    }

    public function addInternalNote(ChatbotConversation $conversation, int $agentId, string $message): ChatbotHistory
    {
        $this->claim($conversation, $agentId);

        return ChatbotHistory::query()->create([
            'user_id' => $agentId,
            'chatbot_id' => $conversation->chatbot_id,
            'conversation_id' => $conversation->id,
            'message' => $message,
            'role' => 'assistant',
            'model' => 'internal-note',
            'message_type' => 'note',
            'is_internal_note' => true,
            'created_at' => now(),
            'team_id' => $conversation->team_id,
            'company_id' => $conversation->company_id,
        ]);
    }

    public function transfer(ChatbotConversation $conversation, int $fromAgentId, int $toAgentId, ?string $reason = null): ChatbotConversation
    {
        DB::transaction(function () use ($conversation, $fromAgentId, $toAgentId, $reason) {
            $conversation->forceFill([
                'assigned_agent_id' => $toAgentId,
                'last_activity_at' => now(),
                'closed' => false,
                'closed_at' => null,
            ])->save();

            $this->addInternalNote(
                $conversation,
                $fromAgentId,
                trim(($reason ? "Transfer note: {$reason}. " : '') . "Transferred to agent #{$toAgentId}")
            );
        });

        return $conversation->fresh(['assignedAgent']);
    }

    public function close(ChatbotConversation $conversation, int $agentId, ?string $reason = null): ChatbotConversation
    {
        DB::transaction(function () use ($conversation, $agentId, $reason) {
            $conversation->forceFill([
                'closed' => true,
                'closed_at' => now(),
                'last_activity_at' => now(),
            ])->save();

            if ($reason) {
                $this->addInternalNote($conversation, $agentId, 'Closed conversation: ' . $reason);
            }
        });

        return $conversation;
    }

    public function customerMessage(ChatbotConversation $conversation, int $userId, string $message, ?UploadedFile $attachment = null): ChatbotHistory
    {
        return DB::transaction(function () use ($conversation, $userId, $message, $attachment) {
            $attachmentPath = $attachment?->store('chatbot-attachments', 'public');

            $history = ChatbotHistory::query()->create([
                'user_id' => $userId,
                'chatbot_id' => $conversation->chatbot_id,
                'conversation_id' => $conversation->id,
                'message' => $message,
                'media_name' => $attachmentPath,
                'role' => 'user',
                'model' => 'customer-portal',
                'message_type' => $attachmentPath ? 'file' : 'text',
                'created_at' => now(),
                'team_id' => $conversation->team_id,
                'company_id' => $conversation->company_id,
            ]);

            $conversation->forceFill([
                'last_activity_at' => now(),
                'customer_read_at' => now(),
                'closed' => false,
                'closed_at' => null,
            ])->save();

            return $history;
        });
    }
}
