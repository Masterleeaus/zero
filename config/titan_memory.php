<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TitanMemoryService
    |--------------------------------------------------------------------------
    |
    | Configuration for the canonical TitanCore memory layer.
    | Aligns with docs/titancore/15_MEMORY_EMBEDDING_STRATEGY.md
    | and docs/titancore/08_MEMORY_SIGNAL_REWIND_PLAN.md
    |
    */

    'enabled' => true,

    'tenant_key' => 'company_id',

    /*
    |--------------------------------------------------------------------------
    | Default Memory Recall Limit
    |--------------------------------------------------------------------------
    */

    'default_limit' => (int) env('TITAN_MEMORY_DEFAULT_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | Importance Score Thresholds
    |--------------------------------------------------------------------------
    |
    | Memories below the prune threshold are eligible for automatic expiry.
    |
    */

    'importance' => [
        'ai_decision' => 0.7,
        'session_snapshot' => 1.0,
        'handoff' => 0.8,
        'general' => 0.5,
        'prune_threshold' => (float) env('TITAN_MEMORY_PRUNE_THRESHOLD', 0.2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Handoff TTL
    |--------------------------------------------------------------------------
    |
    | Number of hours before a session handoff record expires.
    |
    */

    'handoff_ttl_hours' => (int) env('TITAN_MEMORY_HANDOFF_TTL', 24),

    /*
    |--------------------------------------------------------------------------
    | Vector Substrate (laravel-rag bridge)
    |--------------------------------------------------------------------------
    |
    | Enable to activate semantic recall via the VectorMemoryAdapter.
    | TitanMemoryService remains the memory owner. Vector is substrate only.
    |
    */

    'vector' => [
        'enabled' => (bool) env('TITAN_MEMORY_VECTOR_ENABLED', false),
        'max_semantic_results' => (int) env('TITAN_MEMORY_SEMANTIC_LIMIT', 5),
        'embedding_model' => env('TITAN_MEMORY_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimensions' => (int) env('TITAN_MEMORY_EMBEDDING_DIMS', 1536),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rewind Integration
    |--------------------------------------------------------------------------
    |
    | When enabled, TitanMemoryService::snapshot() will link to rewind events
    | and store snapshots in tz_ai_memory_snapshots with rewind_ref.
    |
    */

    'rewind' => [
        'enabled' => (bool) env('TITAN_MEMORY_REWIND_ENABLED', true),
        'auto_snapshot_on_rewind' => (bool) env('TITAN_MEMORY_AUTO_SNAPSHOT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Summarisation
    |--------------------------------------------------------------------------
    |
    | Max memories to include in a summarize() call.
    |
    */

    'summarize_max' => (int) env('TITAN_MEMORY_SUMMARIZE_MAX', 10),

];
