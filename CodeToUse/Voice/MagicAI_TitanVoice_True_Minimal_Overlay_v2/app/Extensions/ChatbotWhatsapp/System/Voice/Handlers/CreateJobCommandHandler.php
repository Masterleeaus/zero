<?php
declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\Contracts\VoiceCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class CreateJobCommandHandler implements VoiceCommandHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        $time = $command->entities['scheduled_for'] ?? 'the requested time';

        return [
            'status' => true,
            'message' => sprintf(
                "I've created a job for %s scheduled for %s.",
                $command->entities['customer_name'] ?? 'the customer',
                $time
            ),
            'intent' => 'create_job',
            'conversation_id' => $conversation->id,
            'payload' => [
                'customer_name' => $command->entities['customer_name'] ?? null,
                'scheduled_for' => $command->entities['scheduled_for'] ?? null,
            ],
        ];
    }
}
