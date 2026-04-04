<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Handlers;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class CreateQuoteHandler implements LifecycleAwareHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return [
            'status' => true,
            'message' => sprintf("I've drafted a quote for %s%s.", $command->entities['customer_name'] ?? 'the customer', isset($command->entities['subject']) ? ' covering '.$command->entities['subject'] : ''),
            'intent' => $command->intent,
            'lifecycle_stage' => 'quote',
            'entities' => $command->entities,
            'conversation_id' => $conversation->id,
        ];
    }
}
