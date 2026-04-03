<?php

/**
 * TitanOmni Configuration
 * 
 * Security hardening, feature flags, rate limiting, and audit logging setup.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Channels Configuration
    |--------------------------------------------------------------------------
    |
    | Supported communication channels and default routing behavior.
    |
    */
    'default_channel' => env('OMNI_DEFAULT_CHANNEL', 'web'),

    'allowed_channels' => [
        'web',       // Embedded chat widget
        'internal',  // Internal team messaging
        'api',       // Direct API calls
        'whatsapp',  // Twilio WhatsApp Business
        'telegram',  // Telegram Bot API
        'messenger', // Facebook Messenger
        'voice',     // PSTN/VoIP calls
    ],

    /*
    |--------------------------------------------------------------------------
    | Dual-Write Configuration
    |--------------------------------------------------------------------------
    |
    | Enable/disable simultaneous writes to Omni and legacy chatbot tables.
    | Useful for zero-downtime migration from old extensions.
    |
    */
    'dual_write_legacy' => env('OMNI_DUAL_WRITE_LEGACY', true),
    'keep_channel_extensions_separate' => true,

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook verification, encryption, and access control settings.
    |
    */
    'enable_webhook_verification' => env('OMNI_WEBHOOK_VERIFICATION', true),

    'webhook_signature_schemes' => [
        'twilio' => 'verifyTwilioSignature',
        'facebook' => 'verifyFacebookSignature',
        'telegram' => 'verifyTelegramSignature',
        'whatsapp' => 'verifyTwilioSignature',
    ],

    // Encrypt bridge credentials at rest
    'encrypt_bridge_credentials' => env('OMNI_ENCRYPT_CREDENTIALS', true),

    // CORS configuration for embedded chat
    'cors_origins' => explode(',', env('OMNI_CORS_ORIGINS', '*')),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Endpoint-specific rate limits (requests per minute per user/company).
    | Prevents abuse and DoS attacks.
    |
    */
    'rate_limits' => [
        'conversation.store' => [
            'requests' => (int) env('OMNI_RATE_LIMIT_STORE', 100),
            'decay' => 1,
        ],
        'conversation.read' => [
            'requests' => (int) env('OMNI_RATE_LIMIT_READ', 300),
            'decay' => 1,
        ],
        'voice.inbound' => [
            'requests' => (int) env('OMNI_RATE_LIMIT_VOICE_IN', 10),
            'decay' => 1,
        ],
        'voice.callback' => [
            'requests' => (int) env('OMNI_RATE_LIMIT_VOICE_CB', 5),
            'decay' => 1,
        ],
        'knowledge.search' => [
            'requests' => (int) env('OMNI_RATE_LIMIT_KB', 200),
            'decay' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Enable comprehensive audit trail for compliance and forensics.
    |
    */
    'enable_audit_logging' => env('OMNI_AUDIT_LOGGING', true),

    'audit_log_events' => [
        'conversation.created',
        'conversation.updated',
        'message.appended',
        'bridge.configured',
        'knowledge.indexed',
        'call.initiated',
        'call.completed',
    ],

    'audit_retention_days' => (int) env('OMNI_AUDIT_RETENTION', 90), // Keep 90 days

    /*
    |--------------------------------------------------------------------------
    | Message & Media Configuration
    |--------------------------------------------------------------------------
    |
    | Size limits, file type restrictions, and retention policies.
    |
    */
    'max_message_length' => (int) env('OMNI_MAX_MESSAGE_LENGTH', 10000),
    'max_media_size_bytes' => (int) env('OMNI_MAX_MEDIA_SIZE', 100 * 1024 * 1024), // 100 MB
    'max_voice_duration_seconds' => (int) env('OMNI_MAX_VOICE_DURATION', 300), // 5 minutes

    'allowed_media_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],

    'message_retention_days' => (int) env('OMNI_MESSAGE_RETENTION', 365), // 1 year

    /*
    |--------------------------------------------------------------------------
    | Knowledge Base Configuration
    |--------------------------------------------------------------------------
    |
    | Semantic search, indexing, and training settings.
    |
    */
    'knowledge_sources' => ['text', 'pdf', 'website', 'api'],

    'enable_knowledge_indexing' => env('OMNI_KB_INDEXING', true),

    'knowledge_cache_ttl' => (int) env('OMNI_KB_CACHE_TTL', 3600), // 1 hour

    'knowledge_search_limit' => (int) env('OMNI_KB_SEARCH_LIMIT', 10), // Top 10 results

    /*
    |--------------------------------------------------------------------------
    | Voice Configuration
    |--------------------------------------------------------------------------
    |
    | PSTN call handling, TTS/STT services, and recording settings.
    |
    */
    'voice_providers' => [
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_FROM_NUMBER'),
        ],
        'elevenlabs' => [
            'api_key' => env('ELEVENLABS_API_KEY'),
            'voice_id' => env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'),
        ],
    ],

    'enable_call_recording' => env('OMNI_CALL_RECORDING', true),
    'call_recording_storage' => env('OMNI_CALL_STORAGE', 's3'), // 's3' or 'local'
    'call_recording_retention_days' => (int) env('OMNI_CALL_RETENTION', 30),

    'transcription_service' => env('OMNI_TRANSCRIPTION_SERVICE', 'twilio'), // 'twilio' or 'deepgram'

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Observability
    |--------------------------------------------------------------------------
    |
    | Error tracking, metrics collection, and alerting.
    |
    */
    'enable_error_reporting' => env('OMNI_ERROR_REPORTING', true),
    'error_reporter' => env('OMNI_ERROR_REPORTER', 'sentry'), // 'sentry', 'bugsnag', etc.

    'metrics_collection' => env('OMNI_METRICS', true),
    'metrics_backend' => env('OMNI_METRICS_BACKEND', 'datadog'), // 'datadog', 'prometheus', etc.

    'alert_thresholds' => [
        'error_rate_percent' => 5,           // Alert if 5% of requests error
        'response_time_ms' => 2000,          // Alert if avg response > 2s
        'webhook_failure_percent' => 10,     // Alert if 10% of webhooks fail
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    */
    'enable_debug_mode' => env('OMNI_DEBUG', false),

    'conversation_pagination_size' => (int) env('OMNI_PAGE_SIZE', 25),

    'async_processing' => env('OMNI_ASYNC', true), // Queue voice transcription, KB indexing
    'async_queue' => env('OMNI_QUEUE', 'default'),

    // Timeout for external service calls (Twilio, ElevenLabs, etc.)
    'external_service_timeout' => (int) env('OMNI_TIMEOUT', 30),
];
