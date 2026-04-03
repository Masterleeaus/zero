<?php

namespace App\Services\TitanZeroPwaSystem;

use App\Models\TzPwaDevice;
use App\Models\TzPwaSignalIngress;
use App\Services\TitanCoreConsensus\TriCoreConsensus;

class TitanPwaSyncService
{
    public function __construct(
        private readonly SignalEnvelopeBuilder $envelopeBuilder,
        private readonly TriCoreConsensus $consensus,
    ) {}

    /**
     * Ingest a batch of signals from a PWA node.
     *
     * @param  array[]  $signals
     * @return array[]
     */
    public function ingest(array $signals, string $nodeId, int $companyId, int $userId): array
    {
        $device = TzPwaDevice::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->first();

        $results = [];

        foreach ($signals as $signal) {
            $signal = (array) $signal;

            $validation = $this->consensus->validate($signal, $device);

            if (! $validation['passed']) {
                $results[] = [
                    'signal_key' => $signal['signal_key'] ?? null,
                    'status'     => 'rejected',
                    'reason'     => $validation['reason'],
                    'score'      => $validation['score'],
                ];
                continue;
            }

            $ingress = TzPwaSignalIngress::create([
                'node_id'          => $nodeId,
                'company_id'       => $companyId,
                'user_id'          => $userId,
                'signal_key'       => $signal['signal_key'] ?? 'pwa.ingress',
                'payload'          => $signal['payload'] ?? [],
                'signature'        => $signal['signature'] ?? null,
                'timestamp'        => $signal['timestamp'] ?? now(),
                'signal_stage'     => $signal['signal_stage'] ?? 'pending',
                'consensus_score'  => $validation['score'],
                'consensus_passed' => true,
                'meta'             => $signal['meta'] ?? [],
            ]);

            $envelope = $this->envelopeBuilder->fromIngressPayload($signal, $nodeId);

            $ingress->update(['envelope' => $envelope]);

            $results[] = [
                'signal_key' => $signal['signal_key'] ?? null,
                'status'     => 'accepted',
                'ingress_id' => $ingress->id,
                'score'      => $validation['score'],
                'envelope'   => $envelope,
            ];
        }

        // Refresh device last_seen_at
        if ($device) {
            $device->update(['last_seen_at' => now()]);
        }

        return $results;
    }

    /**
     * Register or update a device record (handshake).
     */
    public function handshake(array $deviceData, int $companyId, int $userId): array
    {
        $nodeId = $deviceData['node_id'];

        $device = TzPwaDevice::updateOrCreate(
            ['node_id' => $nodeId, 'company_id' => $companyId],
            [
                'user_id'      => $userId,
                'platform'     => $deviceData['platform'] ?? 'unknown',
                'app_version'  => $deviceData['app_version'] ?? null,
                'device_label' => $deviceData['device_label'] ?? null,
                'node_origin'  => $deviceData['node_origin'] ?? null,
                'trust_level'  => $deviceData['trust_level'] ?? 'provisional',
                'last_seen_at' => now(),
                'meta_json'    => array_merge(
                    $deviceData['meta'] ?? [],
                    ['handshake_at' => now()->toIso8601String()]
                ),
            ]
        );

        return [
            'node_id'     => $device->node_id,
            'company_id'  => $device->company_id,
            'trust_level' => $device->trust_level,
            'platform'    => $device->platform,
            'registered'  => $device->wasRecentlyCreated,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
        ];
    }

    /**
     * Return sync status for a device.
     */
    public function status(string $nodeId, int $companyId): array
    {
        $device = TzPwaDevice::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->first();

        if (! $device) {
            return [
                'node_id'    => $nodeId,
                'registered' => false,
                'status'     => 'unknown',
            ];
        }

        $pendingCount = TzPwaSignalIngress::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->where('signal_stage', 'pending')
            ->count();

        $totalCount = TzPwaSignalIngress::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->count();

        return [
            'node_id'       => $nodeId,
            'registered'    => true,
            'trust_level'   => $device->trust_level,
            'platform'      => $device->platform,
            'last_seen_at'  => $device->last_seen_at?->toIso8601String(),
            'signal_counts' => [
                'total'   => $totalCount,
                'pending' => $pendingCount,
            ],
            'status' => 'active',
        ];
    }
}
