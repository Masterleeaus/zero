<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;

class AiFallbackService
{
    public function respond(string $transcript, ChatbotConversation $conversation, ?string $persona = null): string
    {
        $personaLabel = ucfirst((string) ($persona ?: 'assistant'));

        return sprintf('%s heard "%s". I could not confidently execute that as a command, so I am treating it as a conversational request.', $personaLabel, trim($transcript));
    }
}
