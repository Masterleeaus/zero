<?php

declare(strict_types=1);

namespace App\Services\Drivers;

/**
 * WhatsAppTwilioDriver — Twilio WhatsApp transport driver.
 *
 * Extends SmsDriver to reuse Twilio authentication and dispatch patterns.
 * Overrides getChannelType() and normalize() to handle the 'whatsapp:' prefix
 * Twilio adds to WhatsApp numbers.
 */
class WhatsAppTwilioDriver extends SmsDriver
{
    public function getChannelType(): string
    {
        return 'whatsapp_twilio';
    }

    public function normalize(array $headers, string $rawBody): array
    {
        parse_str($rawBody, $params);

        $from = $params['From'] ?? '';

        // Strip the 'whatsapp:' prefix Twilio prepends to WhatsApp numbers.
        if (str_starts_with($from, 'whatsapp:')) {
            $from = substr($from, strlen('whatsapp:'));
        }

        return [
            'channel'            => $this->getChannelType(),
            'from'               => $from,
            'body'               => $params['Body'] ?? '',
            'provider_message_id' => $params['MessageSid'] ?? '',
            'raw'                => $params,
        ];
    }
}
