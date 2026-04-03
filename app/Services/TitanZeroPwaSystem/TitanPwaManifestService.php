<?php

namespace App\Services\TitanZeroPwaSystem;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class TitanPwaManifestService
{
    /**
     * Return the full PWA web app manifest as an array.
     */
    public function manifest(): array
    {
        $appName = Config::get('app.name', 'TitanZero');

        return [
            'name'             => $appName,
            'short_name'       => Config::get('pwa.short_name', 'TitanZero'),
            'description'      => Config::get('pwa.description', 'AI-first service business operating system'),
            'start_url'        => Config::get('pwa.start_url', '/'),
            'display'          => Config::get('pwa.display', 'standalone'),
            'background_color' => Config::get('pwa.background_color', '#0f172a'),
            'theme_color'      => Config::get('pwa.theme_color', '#6366f1'),
            'orientation'      => Config::get('pwa.orientation', 'portrait-primary'),
            'scope'            => Config::get('pwa.scope', '/'),
            'icons'            => Config::get('pwa.icons', [
                [
                    'src'   => '/pwa/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type'  => 'image/png',
                ],
                [
                    'src'   => '/pwa/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type'  => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ]),
            'categories'       => ['business', 'productivity'],
            'lang'             => Config::get('app.locale', 'en'),
        ];
    }

    /**
     * Return the client-side bootstrap config injected into the PWA shell.
     */
    public function bootstrapConfig(): array
    {
        $serverTime = now();

        return [
            // Identity
            'csrfToken'         => csrf_token(),
            'appVersion'        => Config::get('app.version', Config::get('pwa.version', '1.0.0')),

            // Endpoints
            'apiBase'               => '/pwa',
            'syncEndpoint'          => '/pwa/signals/ingest',
            'statusEndpoint'        => '/pwa/sync/status',
            'handshakeEndpoint'     => '/pwa/handshake',
            'diagnosticsEndpoint'   => '/pwa/diagnostics',

            // Sync policy
            'syncInterval'      => Config::get('pwa.sync_interval', 30000),
            'syncBatchLimit'    => Config::get('pwa.batch_limit', 50),
            'syncRetryLimit'    => Config::get('pwa.retry_limit', 3),
            'syncRetryBackoff'  => Config::get('pwa.retry_backoff', [1000, 5000, 15000]),

            // Trust & node identity
            'trustLevel'        => null, // populated client-side after handshake
            'nodeId'            => null, // populated client-side from IndexedDB

            // Time synchronisation
            'serverTimeUtc'     => $serverTime->toIso8601String(),
            'serverTimeMs'      => (int) ($serverTime->getPreciseTimestamp(3)),

            // Offline capabilities
            'offlineFeaturesEnabled' => Config::get('pwa.offline_features', true),
            'queueCapacity'          => Config::get('pwa.queue_capacity', 500),
            'stagingEnabled'         => Config::get('pwa.staging_enabled', true),

            // Feature flags
            'features' => $this->resolveFeatureFlags(),

            // Asset version for cache-busting
            'runtimeVersion' => Config::get('pwa.runtime_version', '2'),
        ];
    }

    private function resolveFeatureFlags(): array
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
        ]);
    }
}
