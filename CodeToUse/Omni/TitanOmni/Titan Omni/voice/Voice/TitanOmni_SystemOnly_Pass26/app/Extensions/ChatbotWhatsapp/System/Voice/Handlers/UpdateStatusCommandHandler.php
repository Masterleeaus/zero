<?php
declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\Contracts\VoiceCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class UpdateStatusCommandHandler implements VoiceCommandHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        $status = $command->entities['status'] ?? 'updated';

        return [
            'status' => true,
            'message' => sprintf("I've marked the status as %s.", $status),
            'intent' => 'update_status',
            'conversation_id' => $conversation->id,
            'payload' => [
                'status' => $status,
            ],
        ];
    }
}
