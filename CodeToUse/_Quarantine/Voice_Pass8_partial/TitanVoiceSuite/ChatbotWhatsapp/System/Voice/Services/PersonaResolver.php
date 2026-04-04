<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;

class PersonaResolver
{
    public function resolve(ChatbotConversation $conversation, string $channel, ?string $intent = null): string
    {
        $personas = (array) config('titan-personas.personas', []);
        foreach ($personas as $key => $persona) {
            $channels = (array) ($persona['channels'] ?? []);
            $intents = (array) ($persona['intents'] ?? []);
            if (in_array($channel, $channels, true) && ($intent === null || in_array($intent, $intents, true))) {
                return (string) $key;
            }
        }

        return (string) config('titan-personas.default', 'nexus');
    }
}
