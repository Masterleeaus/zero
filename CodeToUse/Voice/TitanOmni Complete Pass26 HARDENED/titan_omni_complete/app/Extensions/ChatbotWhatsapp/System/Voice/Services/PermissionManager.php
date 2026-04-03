<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class PermissionManager
{
    public function canExecute(VoiceCommand $command, ChatbotConversation $conversation): bool
    {
        if ($command->intent === 'unknown') {
            return false;
        }

        return true;
    }
}
