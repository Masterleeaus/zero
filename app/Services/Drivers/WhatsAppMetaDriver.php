<?php

declare(strict_types=1);

namespace App\Services\Drivers;

use App\Contracts\Omni\DeliveryStatusContract;
use App\Contracts\Omni\InboundDriverContract;
use App\Contracts\Omni\OutboundDriverContract;

/**
 * WhatsAppMetaDriver — Meta WhatsApp Cloud API transport driver.
 *
 * Handles outbound messages, inbound webhook verification (hub challenge +
 * X-Hub-Signature-256), payload normalisation, and delivery status parsing.
 *
 * External Meta API calls are isolated in dispatchToProvider() so tests
 * can override that method without making real HTTP calls.
 */
class WhatsAppMetaDriver extends AbstractOmniDriver implements
    OutboundDriverContract,
    InboundDriverContract,
    DeliveryStatusContract
{
    public function getChannelType(): string
    {
        return 'whatsapp_meta';
    }

    protected function requiredConfigKeys(): array
    {
        return ['app_id', 'app_secret', 'verify_token'];
    }

    // ── Outbound ─────────────────────────────────────────────────────────────

    public function send(array $payload): array
    {
        $this->log('send', ['to' => $payload['to'] ?? null]);

        $outbound = [
            'messaging_product' => 'whatsapp',
            'to'                => $payload['to'] ?? '',
            'type'              => $payload['type'] ?? 'text',
            'text'              => ['body' => $payload['body'] ?? ''],
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
     * Override in tests to avoid real Meta API calls.
     *
     * @param array<string, mixed> $payload
     * @return array{status: string, provider_message_id: string|null, raw: mixed}
     */
    protected function dispatchToProvider(array $payload): array
    {
        $this->log('dispatch', ['to' => $payload['to'] ?? null]);

        return [
            'status'              => 'queued',
            'provider_message_id' => null,
            'raw'                 => $payload,
        ];
    }

    // ── Inbound ──────────────────────────────────────────────────────────────

    public function verify(array $headers, string $rawBody): bool
    {
        // Hub challenge verification (GET subscription confirmation)
        if (isset($headers['hub.verify_token'])) {
            return $headers['hub.verify_token'] === ($this->config['verify_token'] ?? '');
        }

        // Webhook payload verification via X-Hub-Signature-256
        $signature = $headers['X-Hub-Signature-256'] ?? $headers['x-hub-signature-256'] ?? '';

        if ($signature === '') {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $this->config['app_secret'] ?? '');

        return hash_equals($expected, $signature);
    }

    public function normalize(array $headers, string $rawBody): array
    {
        $data    = json_decode($rawBody, true) ?? [];
        $entry   = $data['entry'][0] ?? [];
        $change  = $entry['changes'][0] ?? [];
        $value   = $change['value'] ?? [];
        $message = ($value['messages'][0]) ?? [];

        return [
            'channel'            => $this->getChannelType(),
            'from'               => $message['from'] ?? '',
            'body'               => $message['text']['body'] ?? '',
            'provider_message_id' => $message['id'] ?? '',
            'phone_number_id'    => $value['metadata']['phone_number_id'] ?? '',
            'raw'                => $data,
        ];
    }

    // ── Delivery status ───────────────────────────────────────────────────────

    public function parseStatus(array $payload): array
    {
        $entry   = $payload['entry'][0] ?? [];
        $change  = $entry['changes'][0] ?? [];
        $value   = $change['value'] ?? [];
        $status  = $value['statuses'][0] ?? [];

        return [
            'provider_message_id' => $status['id'] ?? null,
            'status'              => $status['status'] ?? 'unknown',
            'timestamp'           => $status['timestamp'] ?? null,
            'raw'                 => $payload,
        ];
    }
}
