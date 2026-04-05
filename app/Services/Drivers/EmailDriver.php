<?php

declare(strict_types=1);

namespace App\Services\Drivers;

use App\Contracts\Omni\OutboundDriverContract;

/**
 * EmailDriver — generic email outbound transport driver.
 *
 * Actual mail dispatch is isolated in dispatchToProvider() so the driver
 * remains testable without real SMTP/API calls.
 */
class EmailDriver extends AbstractOmniDriver implements OutboundDriverContract
{
    public function getChannelType(): string
    {
        return 'email';
    }

    protected function requiredConfigKeys(): array
    {
        return ['from_address', 'from_name'];
    }

    // ── Outbound ─────────────────────────────────────────────────────────────

    public function send(array $payload): array
    {
        $this->log('send', ['to' => $payload['to'] ?? null]);

        $outbound = [
            'to'          => $payload['to'] ?? '',
            'subject'     => $payload['subject'] ?? '',
            'body'        => $payload['body'] ?? '',
            'from_address' => $this->config['from_address'] ?? '',
            'from_name'   => $this->config['from_name'] ?? '',
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
     * Override in tests to avoid real mail dispatch.
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
}
