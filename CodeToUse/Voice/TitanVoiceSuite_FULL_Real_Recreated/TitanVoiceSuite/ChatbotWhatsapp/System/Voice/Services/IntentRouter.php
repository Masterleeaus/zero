<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\GenericCommandHandler;

class IntentRouter
{
    public function __construct(
        protected GenericCommandHandler $handler,
    ) {}

    public function route(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return $this->handler->handle($command, $conversation);
    }
}
