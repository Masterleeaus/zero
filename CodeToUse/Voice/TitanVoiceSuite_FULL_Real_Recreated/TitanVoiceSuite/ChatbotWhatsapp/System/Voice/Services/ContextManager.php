<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use Illuminate\Support\Arr;

class ContextManager
{
    public function rememberPending(ChatbotConversation $conversation, array $payload): void
    {
        $context = (array) ($conversation->voice_command_context ?? []);
        $context['pending'] = $payload;
        $conversation->forceFill(['voice_command_context' => $context])->save();
    }

    public function clearPending(ChatbotConversation $conversation): void
    {
        $context = (array) ($conversation->voice_command_context ?? []);
        unset($context['pending']);
        $conversation->forceFill(['voice_command_context' => $context])->save();
    }

    public function getPending(ChatbotConversation $conversation): ?array
    {
        return Arr::get((array) ($conversation->voice_command_context ?? []), 'pending');
    }
}
