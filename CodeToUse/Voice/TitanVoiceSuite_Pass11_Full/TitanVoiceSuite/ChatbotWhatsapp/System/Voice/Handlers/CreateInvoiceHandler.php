<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class CreateInvoiceHandler implements LifecycleAwareHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return [
            'status' => true,
            'message' => sprintf("I've created an invoice draft for %s.", $command->entities['customer_name'] ?? 'the customer'),
            'intent' => $command->intent,
            'lifecycle_stage' => 'invoice',
            'entities' => $command->entities,
            'conversation_id' => $conversation->id,
        ];
    }
}
