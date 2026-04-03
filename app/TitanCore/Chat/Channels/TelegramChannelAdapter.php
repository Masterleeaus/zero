<?php

namespace App\TitanCore\Chat\Channels;

use App\TitanCore\Chat\Contracts\ChannelAdapterContract;
use Illuminate\Support\Str;

/**
 * TelegramChannelAdapter
 *
 * Adapter for Telegram channel (ChatbotTelegram extension).
 *
 * Source: CodeToUse/Extensions/ExtensionLibrary/ChatbotTelegram
 */
class TelegramChannelAdapter implements ChannelAdapterContract
{
    public function channel(): string
    {
        return 'telegram';
    }

    public function toEnvelope(array $payload): array
    {
        $message = $payload['message'] ?? [];

        return [
            'id'         => (string) Str::uuid(),
            'intent'     => 'chat.complete',
            'stage'      => 'suggestion',
            'channel'    => $this->channel(),
            'surface'    => 'chatbot',
            'input'      => $message['text'] ?? '',
            'company_id' => $payload['company_id'] ?? null,
            'user_id'    => $payload['user_id'] ?? null,
            'session_id' => $payload['session_id'] ?? (string) ($message['chat']['id'] ?? Str::uuid()),
            'meta'       => [
                'chat_id'    => $message['chat']['id'] ?? null,
                'from'       => $message['from'] ?? null,
                'message_id' => $message['message_id'] ?? null,
            ],
        ];
    }

    public function sendResponse(array $result, array $context): void
    {
        $responseText = $result['output'] ?? $result['decision']['output'] ?? '';
        $chatId = $context['message']['chat']['id'] ?? null;
        if (empty($responseText) || empty($chatId)) {
            return;
        }

        // Delegate to the installed ChatbotTelegram extension if present.
        if (class_exists(\App\Extensions\ChatbotTelegram\System\Services\TelegramService::class)) {
            app(\App\Extensions\ChatbotTelegram\System\Services\TelegramService::class)
                ->sendMessage($chatId, $responseText);
        }
    }
}
