<?php

namespace App\Services\TitanZeroPwaSystem;

use App\Titan\Signals\EnvelopeBuilder;

class SignalEnvelopeBuilder
{
    public function __construct(
        private readonly EnvelopeBuilder $coreBuilder,
    ) {}

    /**
     * Wrap a single ingress payload into a normalized signal envelope.
     */
    public function fromIngressPayload(array $ingress, string $nodeId): array
    {
        $signalKey = $ingress['signal_key'] ?? 'pwa.ingress';
        $payload   = $ingress['payload'] ?? [];
        $signature = $ingress['signature'] ?? null;
        $timestamp = $ingress['timestamp'] ?? now()->toIso8601String();

        $envelope = $this->coreBuilder->build([
            'origin'     => 'pwa-node:'.$nodeId,
            'summary'    => 'PWA ingress signal: '.$signalKey,
            'signals'    => [
                [
                    'id'         => $ingress['id'] ?? ('pwa-'.uniqid('', true)),
                    'type'       => $signalKey,
                    'kind'       => 'pwa_ingress',
                    'severity'   => $ingress['severity'] ?? 'GREEN',
                    'status'     => $ingress['signal_stage'] ?? 'pending',
                    'payload'    => $payload,
                    'timestamp'  => $timestamp,
                    'priority'   => ['band' => 'normal'],
                    'meta'       => [
                        'node_id'   => $nodeId,
                        'signature' => $signature,
                    ],
                ],
            ],
            'meta' => [
                'node_id'   => $nodeId,
                'signature' => $signature,
            ],
        ]);

        return [
            'node_id'          => $nodeId,
            'signal_key'       => $signalKey,
            'origin'           => $envelope['origin'] ?? ('pwa-node:'.$nodeId),
            'wrapped_payload'  => $envelope,
            'wrapped_at'       => now()->toIso8601String(),
        ];
    }

    /**
     * Process a batch of ingress payloads into envelopes.
     *
     * @param  array[]  $ingressBatch
     * @return array[]
     */
    public function buildBatch(array $ingressBatch, string $nodeId): array
    {
        $results = [];
        foreach ($ingressBatch as $ingress) {
            $results[] = $this->fromIngressPayload((array) $ingress, $nodeId);
        }

        return $results;
    }
}
