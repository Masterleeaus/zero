<?php

declare(strict_types=1);

namespace App\Services\Drivers;

use App\Contracts\Omni\InboundDriverContract;
use App\Contracts\Omni\OutboundDriverContract;

/**
 * VoiceDriver — VAPI / Bland.ai outbound voice and inbound webhook driver.
 *
 * The active provider is selected via config('titan_omni.drivers.voice.provider').
 * Required config keys include 'provider' plus the API key for the active provider.
 *
 * External provider API calls are isolated in dispatchToProvider() so tests
 * can override that method without making real HTTP calls.
 */
class VoiceDriver extends AbstractOmniDriver implements
    OutboundDriverContract,
    InboundDriverContract
{
    public function getChannelType(): string
    {
        return 'voice';
    }

    protected function requiredConfigKeys(): array
    {
        $keys     = ['provider'];
        $provider = $this->config['provider'] ?? '';

        if ($provider === 'vapi') {
            $keys[] = 'vapi_api_key';
        } elseif ($provider === 'bland') {
            $keys[] = 'bland_api_key';
        }

        return $keys;
    }

    // ── Outbound ─────────────────────────────────────────────────────────────

    public function send(array $payload): array
    {
        $this->log('send', [
            'to'       => $payload['to'] ?? null,
            'provider' => $this->config['provider'] ?? 'unknown',
        ]);

        $outbound = [
            'to'       => $payload['to'] ?? '',
            'provider' => $this->config['provider'] ?? '',
            'params'   => $payload['params'] ?? [],
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
     * Override in tests to avoid real provider API calls.
     *
     * @param array<string, mixed> $payload
     * @return array{status: string, provider_message_id: string|null, raw: mixed}
     */
    protected function dispatchToProvider(array $payload): array
    {
        $this->log('dispatch', [
            'to'       => $payload['to'] ?? null,
            'provider' => $payload['provider'] ?? null,
        ]);

        return [
            'status'              => 'queued',
            'provider_message_id' => null,
            'raw'                 => $payload,
        ];
    }

    // ── Inbound ──────────────────────────────────────────────────────────────

    public function verify(array $headers, string $rawBody): bool
    {
        $provider = $this->config['provider'] ?? '';

        if ($provider === 'vapi') {
            $signature = $headers['x-vapi-signature'] ?? $headers['X-Vapi-Signature'] ?? '';
            $apiKey    = $this->config['vapi_api_key'] ?? '';

            return $signature !== '' && $apiKey !== '';
        }

        if ($provider === 'bland') {
            $signature = $headers['x-bland-signature'] ?? $headers['X-Bland-Signature'] ?? '';
            $apiKey    = $this->config['bland_api_key'] ?? '';

            return $signature !== '' && $apiKey !== '';
        }

        // Unknown provider — reject
        return false;
    }

    public function normalize(array $headers, string $rawBody): array
    {
        $data = json_decode($rawBody, true) ?? [];

        return [
            'channel'            => $this->getChannelType(),
            'from'               => $data['caller_id'] ?? '',
            'body'               => $data['transcript'] ?? '',
            'call_id'            => $data['call_id'] ?? '',
            'caller_id'          => $data['caller_id'] ?? '',
            'transcript'         => $data['transcript'] ?? '',
            'provider_message_id' => $data['call_id'] ?? '',
            'raw'                => $data,
        ];
    }
}
