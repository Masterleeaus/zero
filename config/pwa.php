<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Titan Zero PWA Configuration
    |--------------------------------------------------------------------------
    */

    // App manifest fields
    'short_name'       => env('PWA_SHORT_NAME', 'TitanZero'),
    'description'      => env('PWA_DESCRIPTION', 'AI-first service business operating system'),
    'start_url'        => env('PWA_START_URL', '/'),
    'display'          => env('PWA_DISPLAY', 'standalone'),
    'background_color' => env('PWA_BG_COLOR', '#0f172a'),
    'theme_color'      => env('PWA_THEME_COLOR', '#6366f1'),
    'orientation'      => env('PWA_ORIENTATION', 'portrait-primary'),
    'scope'            => env('PWA_SCOPE', '/'),
    'version'          => env('PWA_VERSION', '3.0.0'),
    'runtime_version'  => env('PWA_RUNTIME_VERSION', '3'),

    // Icons
    'icons' => [
        ['src' => '/pwa/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => '/pwa/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
    ],

    // Sync policy
    'sync_interval'      => env('PWA_SYNC_INTERVAL', 30000),
    'batch_limit'        => env('PWA_BATCH_LIMIT', 50),
    'retry_limit'        => env('PWA_RETRY_LIMIT', 3),
    'retry_backoff'      => [1000, 5000, 15000],
    'deferred_retry_ms'  => env('PWA_DEFERRED_RETRY_MS', 300000),

    // Signing
    'signing_key' => env('PWA_SIGNING_KEY', ''),

    // Offline / staging
    'offline_features'  => env('PWA_OFFLINE_FEATURES', true),
    'queue_capacity'    => env('PWA_QUEUE_CAPACITY', 500),
    'staging_enabled'   => env('PWA_STAGING_ENABLED', true),
    'diagnostics_enabled' => env('PWA_DIAGNOSTICS_ENABLED', true),

    // Feature flags (can be overridden per environment)
    'features' => [
        'offline_sync'         => true,
        'background_sync'      => true,
        'push_notifications'   => false,
        'signal_ingestion'     => true,
        'photo_staging'        => true,
        'note_staging'         => true,
        'proof_staging'        => true,
        'idempotency'          => true,
        'signature_validation' => (bool) env('PWA_SIGNATURE_VALIDATION', false),
        'deferred_replay'      => true,
        'conflict_inspection'  => true,
        'capability_profiling' => true,
    ],

];
