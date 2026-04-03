<?php

namespace App\Services\TitanZeroPwaSystem;

use Illuminate\Support\Facades\Config;

/**
 * PwaRuntimeContractService
 *
 * Generates the rich runtime contract delivered to PWA clients via /pwa/bootstrap.
 *
 * Contract includes:
 * - server identity + time offset anchor
 * - sync policy settings
 * - feature flags
 * - offline capability declarations
 * - diagnostics flag
 * - runtime version
 * - server capability requirements
 */
class PwaRuntimeContractService
{
    /**
     * Build the full runtime contract for the client bootstrap payload.
     */
    public function build(?int $userId = null, ?int $companyId = null): array
    {
        $serverTime = now();

        return [
            // ── Server identity ────────────────────────────────────────────
            'server_version'         => Config::get('app.version', Config::get('pwa.version', '3.0.0')),
            'runtime_version'        => Config::get('pwa.runtime_version', '3'),
            'server_time_utc'        => $serverTime->toIso8601String(),
            'server_time_ms'         => (int) ($serverTime->getPreciseTimestamp(3)),

            // ── Node identity (populated after handshake on client) ────────
            'node_id'                => null,
            'trust_level'            => null,
            'trust_reason'           => null,

            // ── Sync policy ────────────────────────────────────────────────
            'sync_policy'            => $this->resolveSyncPolicy(),

            // ── Feature flags ──────────────────────────────────────────────
            'feature_flags'          => $this->resolveFeatureFlags(),

            // ── Offline capabilities ───────────────────────────────────────
            'offline_features'       => Config::get('pwa.offline_features', true),
            'offline_stores'         => [
                'signals_local',
                'sync_queue',
                'staged_uploads',
                'bootstrap_meta',
                'runtime_meta',
                'capability_profile',
                'conflict_queue',
            ],

            // ── Diagnostics ────────────────────────────────────────────────
            'diagnostics_enabled'    => Config::get('pwa.diagnostics_enabled', true),
            'diagnostics_endpoint'   => '/pwa/diagnostics',
            'conflict_endpoint'      => '/pwa/conflicts',
            'queue_health_endpoint'  => '/pwa/queue/health',

            // ── API endpoints ──────────────────────────────────────────────
            'api_base'               => '/pwa',
            'handshake_endpoint'     => '/pwa/handshake',
            'sync_endpoint'          => '/pwa/signals/ingest',
            'status_endpoint'        => '/pwa/sync/status',
            'staging_endpoint'       => '/pwa/staging/artifacts',

            // ── Browser support requirements ───────────────────────────────
            'browser_support'        => [
                'service_worker'         => true,
                'indexed_db'             => true,
                'background_sync'        => true,
                'push_api'               => false,
                'geolocation'            => true,
                'camera'                 => true,
                'storage_persistence'    => true,
                'web_locks'              => false,
            ],

            // ── CSRF ───────────────────────────────────────────────────────
            'csrf_token'             => csrf_token(),
        ];
    }

    /**
     * Resolve the sync policy block.
     */
    public function resolveSyncPolicy(): array
    {
        return [
            'sync_interval_ms'   => (int) Config::get('pwa.sync_interval', 30000),
            'batch_limit'        => (int) Config::get('pwa.batch_limit', 50),
            'retry_limit'        => (int) Config::get('pwa.retry_limit', 3),
            'queue_limit'        => (int) Config::get('pwa.queue_capacity', 500),
            'retry_backoff_ms'   => Config::get('pwa.retry_backoff', [1000, 5000, 15000]),
            'deferred_retry_ms'  => (int) Config::get('pwa.deferred_retry_ms', 300000),
        ];
    }

    /**
     * Resolve feature flags.
     */
    public function resolveFeatureFlags(): array
    {
        return Config::get('pwa.features', [
            'offline_sync'          => true,
            'background_sync'       => true,
            'push_notifications'    => false,
            'signal_ingestion'      => true,
            'photo_staging'         => true,
            'note_staging'          => true,
            'proof_staging'         => true,
            'idempotency'           => true,
            'signature_validation'  => true,
            'deferred_replay'       => true,
            'conflict_inspection'   => true,
            'capability_profiling'  => true,
        ]);
    }
}
