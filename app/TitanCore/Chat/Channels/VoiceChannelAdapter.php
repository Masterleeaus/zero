<?php

namespace App\TitanCore\Chat\Channels;

use App\TitanCore\Chat\Contracts\ChannelAdapterContract;
use Illuminate\Support\Str;

/**
 * VoiceChannelAdapter
 *
 * Adapter for voice chat channel (ChatbotVoice + ElevenlabsVoiceChat extensions).
 * Translates voice conversation turns (text transcripts) to Titan envelopes.
 *
 * Source: CodeToUse/Extensions/ExtensionLibrary/ChatbotVoice
 */
class VoiceChannelAdapter implements ChannelAdapterContract
{
    public function channel(): string
    {
        return 'voice';
    }

    public function toEnvelope(array $payload): array
    {
        return [
            'id'         => (string) Str::uuid(),
            'intent'     => 'chat.complete',
            'stage'      => 'suggestion',
            'channel'    => $this->channel(),
            'surface'    => 'voice_chatbot',
            'input'      => $payload['transcript'] ?? $payload['input'] ?? '',
            'company_id' => $payload['company_id'] ?? null,
            'user_id'    => $payload['user_id'] ?? null,
            'session_id' => $payload['session_id'] ?? $payload['conversation_id'] ?? (string) Str::uuid(),
            'meta'       => [
                'voice_id'       => $payload['voice_id'] ?? null,
                'chatbot_id'     => $payload['chatbot_id'] ?? null,
                'language'       => $payload['language'] ?? 'en',
                'modality'       => 'voice',
            ],
        ];
    }

    public function sendResponse(array $result, array $context): void
    {
        // Voice response is TTS-rendered by the ElevenLabs / ChatbotVoice extension.
        // This adapter signals that execution is complete; the extension handles TTS synthesis.
    }
}
