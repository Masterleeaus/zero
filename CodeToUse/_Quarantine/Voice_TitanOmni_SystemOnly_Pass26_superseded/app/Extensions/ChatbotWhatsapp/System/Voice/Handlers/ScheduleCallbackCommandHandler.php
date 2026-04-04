<?php
declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\Contracts\VoiceCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class ScheduleCallbackCommandHandler implements VoiceCommandHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return [
            'status' => true,
            'message' => sprintf(
                "I've scheduled a callback for %s.",
                $command->entities['scheduled_for'] ?? 'the requested time'
            ),
            'intent' => 'schedule_callback',
            'conversation_id' => $conversation->id,
            'payload' => [
                'scheduled_for' => $command->entities['scheduled_for'] ?? null,
                'phone' => $conversation->call_phone_number ?? null,
            ],
        ];
    }
}
