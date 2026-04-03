<?php

namespace App\TitanCore\Chat\Contracts;

/**
 * ChannelAdapterContract
 *
 * All chat channel adapters (Messenger, WhatsApp, Voice, Telegram, Webchat, External)
 * MUST implement this contract. Adapters are responsible for:
 *   - translating channel-specific payloads into the Titan envelope format
 *   - sending responses back to the channel-specific transport
 *
 * They must NOT implement their own AI execution, memory, or signal logic.
 * All execution routes through OmniManager → TitanAIRouter → TitanMemory.
 */
interface ChannelAdapterContract
{
    /**
     * The unique channel identifier (e.g. 'messenger', 'whatsapp', 'voice', 'telegram', 'webchat', 'external').
     */
    public function channel(): string;

    /**
     * Translate the raw inbound channel payload into a normalised Titan envelope.
     *
     * Required envelope keys:
     *   - input        (string)  the user's message text
     *   - channel      (string)  channel identifier
     *   - surface      (string)  'chatbot' or similar surface name
     *   - company_id   (int)     tenant boundary
     *   - user_id      (int|null) authenticated user id if known
     *   - session_id   (string)  conversation session id
     *   - intent       (string)  default 'chat.complete'
     *   - stage        (string)  default 'suggestion'
     *
     * @param  array<string, mixed>  $payload  raw inbound channel payload
     * @return array<string, mixed>  titan envelope
     */
    public function toEnvelope(array $payload): array;

    /**
     * Send the Titan response back via the channel-specific transport.
     *
     * @param  array<string, mixed>  $result   result from OmniManager::dispatch()
     * @param  array<string, mixed>  $context  original channel payload / metadata
     */
    public function sendResponse(array $result, array $context): void;
}
