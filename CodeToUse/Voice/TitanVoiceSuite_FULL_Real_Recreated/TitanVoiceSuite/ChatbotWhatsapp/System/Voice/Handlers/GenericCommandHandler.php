<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\Contracts\VoiceCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class GenericCommandHandler implements VoiceCommandHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return match ($command->intent) {
            'create_ticket' => [
                'status' => true,
                'message' => sprintf("I've created a ticket for %s about %s.", $command->entities['customer_name'] ?? 'the customer', $command->entities['subject'] ?? 'the issue'),
                'intent' => $command->intent,
            ],
            'create_job' => [
                'status' => true,
                'message' => sprintf("I've created a job for %s.", $command->entities['customer_name'] ?? 'the customer'),
                'intent' => $command->intent,
            ],
            'list_tasks' => [
                'status' => true,
                'message' => "I've pulled your current tasks.",
                'intent' => $command->intent,
            ],
            'schedule_callback' => [
                'status' => true,
                'message' => sprintf("I've scheduled a callback for %s.", $command->entities['scheduled_for'] ?? 'the requested time'),
                'intent' => $command->intent,
            ],
            'update_status' => [
                'status' => true,
                'message' => "I've updated the status.",
                'intent' => $command->intent,
            ],
            default => [
                'status' => false,
                'message' => 'I could not map that command to an action.',
                'intent' => $command->intent,
            ],
        };
    }
}
