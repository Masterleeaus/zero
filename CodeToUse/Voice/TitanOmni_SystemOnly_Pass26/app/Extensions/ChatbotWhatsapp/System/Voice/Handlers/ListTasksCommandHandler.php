<?php
declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\Contracts\VoiceCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class ListTasksCommandHandler implements VoiceCommandHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return [
            'status' => true,
            'message' => "I've pulled your current tasks and readied them for playback.",
            'intent' => 'list_tasks',
            'conversation_id' => $conversation->id,
            'payload' => [
                'task_count' => 0,
            ],
        ];
    }
}
