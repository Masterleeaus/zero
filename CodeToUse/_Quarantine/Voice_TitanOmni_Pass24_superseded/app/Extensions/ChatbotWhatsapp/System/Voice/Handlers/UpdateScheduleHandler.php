<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class UpdateScheduleHandler implements LifecycleAwareHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return [
            'status' => true,
            'message' => sprintf("I've updated the schedule%s.", isset($command->entities['scheduled_for']) ? ' to '.$command->entities['scheduled_for'] : ''),
            'intent' => $command->intent,
            'lifecycle_stage' => 'planning',
            'entities' => $command->entities,
            'conversation_id' => $conversation->id,
        ];
    }
}
