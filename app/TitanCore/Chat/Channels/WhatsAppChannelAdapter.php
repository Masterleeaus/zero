<?php

namespace App\TitanCore\Chat\Channels;

use App\TitanCore\Chat\Contracts\ChannelAdapterContract;
use Illuminate\Support\Str;

/**
 * WhatsAppChannelAdapter
 *
 * Adapter for WhatsApp / Twilio channel (ChatbotWhatsapp extension).
 * Translates Twilio webhook payloads to Titan envelopes.
 *
 * Source: CodeToUse/Extensions/ExtensionLibrary/ChatbotWhatsapp
 */
class WhatsAppChannelAdapter implements ChannelAdapterContract
{
    public function channel(): string
    {
        return 'whatsapp';
    }

    public function toEnvelope(array $payload): array
    {
        return [
            'id'         => (string) Str::uuid(),
            'intent'     => 'chat.complete',
            'stage'      => 'suggestion',
            'channel'    => $this->channel(),
            'surface'    => 'chatbot',
            'input'      => $payload['Body'] ?? $payload['message'] ?? '',
            'company_id' => $payload['company_id'] ?? null,
            'user_id'    => $payload['user_id'] ?? null,
            'session_id' => $payload['session_id'] ?? $payload['From'] ?? (string) Str::uuid(),
            'meta'       => [
                'from'        => $payload['From'] ?? null,
                'to'          => $payload['To'] ?? null,
                'message_sid' => $payload['MessageSid'] ?? null,
            ],
        ];
    }

    public function sendResponse(array $result, array $context): void
    {
        $responseText = $result['output'] ?? $result['decision']['output'] ?? '';
        if (empty($responseText) || empty($context['From'] ?? null)) {
            return;
        }

        // Delegate to the installed ChatbotWhatsapp extension service if present.
        if (class_exists(\App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioWhatsappService::class)) {
            app(\App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioWhatsappService::class)
                ->sendMessage($context['From'], $responseText);
        }
    }
}
