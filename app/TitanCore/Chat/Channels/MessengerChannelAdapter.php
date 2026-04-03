<?php

namespace App\TitanCore\Chat\Channels;

use App\TitanCore\Chat\Contracts\ChannelAdapterContract;
use Illuminate\Support\Str;

/**
 * MessengerChannelAdapter
 *
 * Adapter for Facebook Messenger channel (ChatbotMessenger extension).
 * Translates Messenger webhook payloads to Titan envelopes and dispatches
 * responses back via the Messenger Send API.
 *
 * Source: CodeToUse/Extensions/ExtensionLibrary/ChatbotMessenger
 */
class MessengerChannelAdapter implements ChannelAdapterContract
{
    public function channel(): string
    {
        return 'messenger';
    }

    public function toEnvelope(array $payload): array
    {
        return [
            'id'         => (string) Str::uuid(),
            'intent'     => 'chat.complete',
            'stage'      => 'suggestion',
            'channel'    => $this->channel(),
            'surface'    => 'chatbot',
            'input'      => $payload['message']['text'] ?? '',
            'company_id' => $payload['company_id'] ?? null,
            'user_id'    => $payload['user_id'] ?? null,
            'session_id' => $payload['session_id'] ?? $payload['sender']['id'] ?? (string) Str::uuid(),
            'meta'       => [
                'sender_id'  => $payload['sender']['id'] ?? null,
                'page_id'    => $payload['recipient']['id'] ?? null,
                'mid'        => $payload['message']['mid'] ?? null,
            ],
        ];
    }

    public function sendResponse(array $result, array $context): void
    {
        // Delegate to the installed ChatbotMessenger extension service if present.
        // The MessengerService (ChatbotMessenger extension) handles the Send API call.
        // This adapter is intentionally thin — no AI execution here.
        $responseText = $result['output'] ?? $result['decision']['output'] ?? '';
        if (empty($responseText) || empty($context['sender']['id'] ?? null)) {
            return;
        }

        // Extension service bridging point — called by ChatbotMessengerWebhookController
        // after routing through OmniManager::dispatch().
        if (class_exists(\App\Extensions\ChatbotMessenger\System\Services\MessengerService::class)) {
            app(\App\Extensions\ChatbotMessenger\System\Services\MessengerService::class)
                ->sendMessage($context['sender']['id'], $responseText);
        }
    }
}
