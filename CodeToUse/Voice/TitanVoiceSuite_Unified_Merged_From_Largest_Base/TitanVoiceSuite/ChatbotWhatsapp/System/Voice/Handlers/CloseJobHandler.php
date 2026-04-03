<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class CloseJobHandler implements LifecycleAwareHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return [
            'status' => true,
            'message' => sprintf("I've closed job %s.", $command->entities['job_reference'] ?? 'the selected job'),
            'intent' => $command->intent,
            'lifecycle_stage' => 'completion',
            'entities' => $command->entities,
            'conversation_id' => $conversation->id,
        ];
    }
}
