<?php

declare(strict_types=1);

namespace App\Services\Drivers;

use App\Contracts\Omni\InboundDriverContract;
use App\Contracts\Omni\OutboundDriverContract;

/**
 * WebchatDriver — internal real-time webchat transport driver.
 *
 * Webchat is always available (no external provider credentials required).
 * Outbound delivery is immediate since it goes through a real-time channel;
 * no external dispatch is needed.
 */
class WebchatDriver extends AbstractOmniDriver implements
    OutboundDriverContract,
    InboundDriverContract
{
    public function getChannelType(): string
    {
        return 'webchat';
    }

    /**
     * Webchat requires no external credentials — always configured.
     */
    public function isConfigured(): bool
    {
        return true;
    }

    // ── Outbound ─────────────────────────────────────────────────────────────

    public function send(array $payload): array
    {
        $this->log('send', ['session_id' => $payload['session_id'] ?? null]);

        return [
            'status'              => 'delivered',
            'provider_message_id' => $payload['session_id'] ?? null,
            'raw'                 => $payload,
        ];
    }

    public function sendBatch(array $payloads): array
    {
        $results = [];
        foreach ($payloads as $payload) {
            $results[] = $this->send($payload);
        }

        return $results;
    }

    // ── Inbound ──────────────────────────────────────────────────────────────

    /**
     * Internal channel — no external signature to verify.
     */
    public function verify(array $headers, string $rawBody): bool
    {
        return true;
    }

    public function normalize(array $headers, string $rawBody): array
    {
        $data = json_decode($rawBody, true) ?? [];

        return [
            'channel'            => $this->getChannelType(),
            'from'               => $data['session_id'] ?? '',
            'body'               => $data['message'] ?? '',
            'customer_name'      => $data['customer_name'] ?? '',
            'session_id'         => $data['session_id'] ?? '',
            'provider_message_id' => $data['session_id'] ?? '',
            'raw'                => $data,
        ];
    }
}
