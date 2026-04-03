<?php

namespace App\Services\TitanZeroPwaSystem;

use App\Jobs\TitanPwa\PromotePwaIngressJob;
use App\Models\TzPwaDevice;
use App\Models\TzPwaSignalIngress;
use App\Services\TitanCoreConsensus\TriCoreConsensus;
use Illuminate\Support\Facades\Log;

class TitanPwaSyncService
{
    public function __construct(
        private readonly SignalEnvelopeBuilder $envelopeBuilder,
        private readonly TriCoreConsensus $consensus,
        private readonly SignalSignatureValidator $signatureValidator,
        private readonly NodeTrustService $trustService,
    ) {}

    /**
     * Ingest a batch of signals from a PWA node.
     *
     * Per-item response codes:
     *   accepted        – stored + queued for canonical promotion
     *   duplicate       – idempotency_key already seen; safe to discard
     *   rejected        – failed consensus or trust gate
     *   invalid_sig     – signature validation failed
     *   rate_limited    – node is throttled
     *   deferred        – node is untrusted but signal queued pending review
     *
     * @param  array[]  $signals
     * @return array[]
     */
    public function ingest(array $signals, string $nodeId, int $companyId, int $userId): array
    {
        $device = TzPwaDevice::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->first();

        // Trust gate: reject entire batch if node is rate-limited or untrusted
        if ($device) {
            $gate = $this->trustService->gate($device);
            if (! $gate['allowed']) {
                return array_map(fn ($s) => [
                    'signal_key'   => $s['signal_key'] ?? null,
                    'status'       => $gate['code'] === 'rate_limited' ? 'rate_limited' : 'rejected',
                    'reason'       => $gate['reason'],
                    'ingest_status' => $gate['code'],
                ], $signals);
            }
        }

        $results = [];

        // Batch idempotency check: collect all non-null keys and check in one query
        $clientKeys = array_values(array_filter(
            array_map(fn ($s) => ($s['idempotency_key'] ?? null), $signals)
        ));

        $existingKeys = $clientKeys
            ? TzPwaSignalIngress::where('node_id', $nodeId)
                ->whereIn('idempotency_key', $clientKeys)
                ->pluck('idempotency_key')
                ->flip()
                ->toArray()
            : [];

        foreach ($signals as $signal) {
            $signal = (array) $signal;
            $idempotencyKey = $signal['idempotency_key'] ?? null;

            // Duplicate check via idempotency_key
            if ($idempotencyKey && isset($existingKeys[$idempotencyKey])) {
                $results[] = [
                    'signal_key'    => $signal['signal_key'] ?? null,
                    'status'        => 'duplicate',
                    'ingest_status' => 'duplicate',
                    'reason'        => 'Idempotency key already ingested',
                ];
                continue;
            }

            // Signature validation
            $sigCheck = $this->signatureValidator->validate(
                $signal,
                $nodeId,
                $device?->signing_key
            );

            if (! $sigCheck['valid'] && $sigCheck['code'] !== 'unverified') {
                if ($device) {
                    $this->trustService->recordFailure($device, $sigCheck['code']);
                    $device->refresh();
                }

                Log::warning('[TitanPwaSyncService] Signature validation failed', [
                    'node_id'    => $nodeId,
                    'signal_key' => $signal['signal_key'] ?? null,
                    'code'       => $sigCheck['code'],
                ]);

                // Store a rejected ingress record for conflict visibility
                TzPwaSignalIngress::create([
                    'node_id'           => $nodeId,
                    'idempotency_key'   => $idempotencyKey,
                    'company_id'        => $companyId,
                    'user_id'           => $userId,
                    'signal_key'        => $signal['signal_key'] ?? 'pwa.ingress',
                    'payload'           => $signal['payload'] ?? [],
                    'signature'         => $signal['signature'] ?? null,
                    'timestamp'         => $signal['timestamp'] ?? now(),
                    'signal_stage'      => 'failed',
                    'ingest_status'     => 'invalid_sig',
                    'failure_reason'    => $sigCheck['reason'],
                    'last_error_code'   => $sigCheck['code'],
                    'conflict_type'     => 'invalid_signature',
                    'consensus_passed'  => false,
                    'server_received_at' => now(),
                ]);

                $results[] = [
                    'signal_key'    => $signal['signal_key'] ?? null,
                    'status'        => 'invalid_sig',
                    'ingest_status' => 'invalid_sig',
                    'reason'        => $sigCheck['reason'],
                ];
                continue;
            }

            // Consensus validation
            $validation = $this->consensus->validate($signal, $device);

            if (! $validation['passed']) {
                // Store a rejected record for conflict visibility
                TzPwaSignalIngress::create([
                    'node_id'           => $nodeId,
                    'idempotency_key'   => $idempotencyKey,
                    'company_id'        => $companyId,
                    'user_id'           => $userId,
                    'signal_key'        => $signal['signal_key'] ?? 'pwa.ingress',
                    'payload'           => $signal['payload'] ?? [],
                    'signature'         => $signal['signature'] ?? null,
                    'timestamp'         => $signal['timestamp'] ?? now(),
                    'signal_stage'      => 'failed',
                    'ingest_status'     => 'rejected',
                    'failure_reason'    => $validation['reason'],
                    'last_error_code'   => 'consensus_failed',
                    'conflict_type'     => 'consensus_fail',
                    'consensus_score'   => $validation['score'],
                    'consensus_passed'  => false,
                    'server_received_at' => now(),
                ]);

                $results[] = [
                    'signal_key'    => $signal['signal_key'] ?? null,
                    'status'        => 'rejected',
                    'ingest_status' => 'rejected',
                    'reason'        => $validation['reason'],
                    'score'         => $validation['score'],
                ];
                continue;
            }

            // Store ingress record
            $ingress = TzPwaSignalIngress::create([
                'node_id'           => $nodeId,
                'idempotency_key'   => $idempotencyKey,
                'company_id'        => $companyId,
                'user_id'           => $userId,
                'signal_key'        => $signal['signal_key'] ?? 'pwa.ingress',
                'payload'           => $signal['payload'] ?? [],
                'signature'         => $signal['signature'] ?? null,
                'timestamp'         => $signal['timestamp'] ?? now(),
                'signal_stage'      => 'pending',
                'ingest_status'     => 'accepted',
                'consensus_score'   => $validation['score'],
                'consensus_passed'  => true,
                'meta'              => $signal['meta'] ?? [],
                'server_received_at' => now(),
            ]);

            // Build + store envelope
            $envelope = $this->envelopeBuilder->fromIngressPayload($signal, $nodeId);
            $ingress->update(['envelope' => $envelope]);

            // Queue canonical signal promotion
            PromotePwaIngressJob::dispatch($ingress->id);

            // Record successful signal for trust tracking
            if ($device) {
                $this->trustService->recordSuccess($device);
            }

            $results[] = [
                'signal_key'    => $signal['signal_key'] ?? null,
                'status'        => 'accepted',
                'ingest_status' => 'accepted',
                'ingress_id'    => $ingress->id,
                'score'         => $validation['score'],
            ];
        }

        // Refresh device last_seen_at and last_sync_at
        if ($device) {
            $device->update([
                'last_seen_at' => now(),
                'last_sync_at' => now(),
            ]);
        }

        return $results;
    }

    /**
     * Register or update a device record (handshake).
     */
    public function handshake(array $deviceData, int $companyId, int $userId): array
    {
        $nodeId = $deviceData['node_id'];

        // Find existing to preserve trust_level and signing_key on updates
        $existing = TzPwaDevice::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->first();

        $device = TzPwaDevice::updateOrCreate(
            ['node_id' => $nodeId, 'company_id' => $companyId],
            [
                'user_id'             => $userId,
                'platform'            => $deviceData['platform'] ?? 'unknown',
                'app_version'         => $deviceData['app_version'] ?? null,
                'device_label'        => $deviceData['device_label'] ?? null,
                'node_origin'         => $deviceData['node_origin'] ?? null,
                'trust_level'         => $existing?->trust_level ?? 'provisional',
                'runtime_version'     => $deviceData['runtime_version'] ?? null,
                'capability_profile'  => $deviceData['capability_profile'] ?? null,
                'capability_tier'     => $deviceData['capability_tier'] ?? null,
                'last_seen_at'        => now(),
                'meta_json'           => array_merge(
                    $deviceData['meta'] ?? [],
                    ['handshake_at' => now()->toIso8601String()]
                ),
            ]
        );

        return [
            'node_id'      => $device->node_id,
            'company_id'   => $device->company_id,
            'trust_level'  => $device->trust_level,
            'platform'     => $device->platform,
            'registered'   => $device->wasRecentlyCreated,
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

        $pendingCount  = TzPwaSignalIngress::where('node_id', $nodeId)->where('company_id', $companyId)->where('signal_stage', 'pending')->count();
        $promotedCount = TzPwaSignalIngress::where('node_id', $nodeId)->where('company_id', $companyId)->where('signal_stage', 'promoted')->count();
        $failedCount   = TzPwaSignalIngress::where('node_id', $nodeId)->where('company_id', $companyId)->where('signal_stage', 'failed')->count();
        $totalCount    = TzPwaSignalIngress::where('node_id', $nodeId)->where('company_id', $companyId)->count();

        return [
            'node_id'       => $nodeId,
            'registered'    => true,
            'trust_level'   => $device->trust_level,
            'is_rate_limited' => (bool) $device->is_rate_limited,
            'platform'      => $device->platform,
            'last_seen_at'  => $device->last_seen_at?->toIso8601String(),
            'signal_counts' => [
                'total'    => $totalCount,
                'pending'  => $pendingCount,
                'promoted' => $promotedCount,
                'failed'   => $failedCount,
            ],
            'status' => $device->is_rate_limited ? 'rate_limited' : 'active',
        ];
    }
}
