<?php

declare(strict_types=1);

namespace App\Services\Drivers;

use App\Contracts\Omni\InboundDriverContract;
use App\Contracts\Omni\OutboundDriverContract;

/**
 * TelegramDriver — Telegram Bot API transport driver.
 *
 * Telegram webhooks are received over HTTPS; there is no default signature
 * header, so verify() always returns true.
 *
 * External Telegram API calls are isolated in dispatchToProvider() so tests
 * can override that method without making real HTTP calls.
 */
class TelegramDriver extends AbstractOmniDriver implements
    OutboundDriverContract,
    InboundDriverContract
{
    public function getChannelType(): string
    {
        return 'telegram';
    }

    protected function requiredConfigKeys(): array
    {
        return ['token'];
    }

    // ── Outbound ─────────────────────────────────────────────────────────────

    public function send(array $payload): array
    {
        $this->log('send', ['chat_id' => $payload['chat_id'] ?? null]);

        $outbound = [
            'chat_id' => $payload['chat_id'] ?? '',
            'text'    => $payload['body'] ?? $payload['text'] ?? '',
        ];

        return $this->dispatchToProvider($outbound);
    }

    public function sendBatch(array $payloads): array
    {
        $results = [];
        foreach ($payloads as $payload) {
            $results[] = $this->send($payload);
        }

        return $results;
    }

    /**
     * Override in tests to avoid real Telegram Bot API calls.
     *
     * @param array<string, mixed> $payload
     * @return array{status: string, provider_message_id: string|null, raw: mixed}
     */
    protected function dispatchToProvider(array $payload): array
    {
        $this->log('dispatch', ['chat_id' => $payload['chat_id'] ?? null]);

        return [
            'status'              => 'queued',
            'provider_message_id' => null,
            'raw'                 => $payload,
        ];
    }

    // ── Inbound ──────────────────────────────────────────────────────────────

    /**
     * Telegram delivers webhooks over HTTPS only — no signature header by default.
     */
    public function verify(array $headers, string $rawBody): bool
    {
        return true;
    }

    public function normalize(array $headers, string $rawBody): array
    {
        $data    = json_decode($rawBody, true) ?? [];
        $message = $data['message'] ?? [];

        return [
            'channel'            => $this->getChannelType(),
            'from'               => (string) ($message['chat']['id'] ?? ''),
            'body'               => $message['text'] ?? '',
            'provider_message_id' => (string) ($message['message_id'] ?? ''),
            'chat_id'            => $message['chat']['id'] ?? null,
            'raw'                => $data,
        ];
    }
}
