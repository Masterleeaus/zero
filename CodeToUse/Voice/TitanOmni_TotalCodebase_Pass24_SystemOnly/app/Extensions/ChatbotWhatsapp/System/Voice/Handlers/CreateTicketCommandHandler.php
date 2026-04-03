<?php
declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\Contracts\VoiceCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class CreateTicketCommandHandler implements VoiceCommandHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return [
            'status' => true,
            'message' => sprintf(
                "I've created a ticket for %s about %s.",
                $command->entities['customer_name'] ?? 'the customer',
                $command->entities['subject'] ?? 'the reported issue'
            ),
            'intent' => 'create_ticket',
            'conversation_id' => $conversation->id,
            'payload' => [
                'customer_name' => $command->entities['customer_name'] ?? null,
                'subject' => $command->entities['subject'] ?? null,
                'priority' => $command->entities['priority'] ?? 'normal',
            ],
        ];
    }
}
