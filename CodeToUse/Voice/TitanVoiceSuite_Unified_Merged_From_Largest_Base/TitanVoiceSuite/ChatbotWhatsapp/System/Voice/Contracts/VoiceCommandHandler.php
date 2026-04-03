<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Contracts;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

interface VoiceCommandHandler
{
    public function handle(VoiceCommand $command, ChatbotConversation $conversation): array;
}
