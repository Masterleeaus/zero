<?php

return [
    'enabled'    => true,
    'ui_surface' => 'resources/views/default/panel/user/business-suite',

    /*
    |--------------------------------------------------------------------------
    | AI Runtime Configuration
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'default_runtime'   => env('TITAN_CORE_DEFAULT_RUNTIME', 'null'),
        'model_router'      => env('TITAN_CORE_MODEL_ROUTER', 'zero'),
        'minimum_confidence' => (float) env('TITAN_CORE_MINIMUM_CONFIDENCE', 0.7),
        'default_text_model'  => env('TITAN_DEFAULT_TEXT_MODEL', 'gpt-4o'),
        'default_image_model' => env('TITAN_DEFAULT_IMAGE_MODEL', 'dall-e-3'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Budget Enforcement (Phase 6.8)
    |--------------------------------------------------------------------------
    */
    'budget' => [
        'daily_limit'            => (int) env('TITAN_AI_DAILY_LIMIT', 100000),
        'per_request_limit'      => (int) env('TITAN_AI_PER_REQUEST_LIMIT', 4096),
        'per_user_daily_limit'   => (int) env('TITAN_AI_PER_USER_DAILY_LIMIT', 10000),
        'per_company_daily_limit' => (int) env('TITAN_AI_PER_COMPANY_DAILY_LIMIT', 50000),
        'fallback_provider'      => env('TITAN_AI_FALLBACK_PROVIDER', 'null'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory Configuration (Phase 6.5)
    |--------------------------------------------------------------------------
    */
    'memory' => [
        'ttl'        => (int) env('TITAN_MEMORY_TTL', 3600),
        'max_tokens' => (int) env('TITAN_MEMORY_MAX_TOKENS', 8192),
        'driver'     => env('TITAN_MEMORY_DRIVER', 'cache'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Zylos Skill Runtime (Phase 6.6)
    |--------------------------------------------------------------------------
    */
    'zylos' => [
        'endpoint' => env('ZYLOS_ENDPOINT', ''),
        'secret'   => env('ZYLOS_SECRET', ''),
        'timeout'  => (int) env('ZYLOS_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Isolation (Phase 6.7)
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'ai'      => env('TITAN_QUEUE_AI', 'titan-ai'),
        'signals' => env('TITAN_QUEUE_SIGNALS', 'titan-signals'),
        'skills'  => env('TITAN_QUEUE_SKILLS', 'titan-skills'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Knowledge Configuration
    |--------------------------------------------------------------------------
    */
    'knowledge' => [
        'mode'          => env('TITAN_CORE_KNOWLEDGE_MODE', 'deferred'),
        'default_scope' => env('TITAN_CORE_KNOWLEDGE_SCOPE', 'tenant'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Source Catalog
    |--------------------------------------------------------------------------
    */
    'sources' => [
        'zero_main' => [
            'key'      => 'zero_main',
            'label'    => 'Host Current Site',
            'priority' => 10,
            'role'     => 'canonical_host',
        ],
        'titan_ai_residual' => [
            'key'      => 'titan_ai_residual',
            'label'    => 'Titan AI Residual Core',
            'priority' => 20,
            'role'     => 'ai_knowledge_memory',
        ],
        'ai_cores' => [
            'key'      => 'ai_cores',
            'label'    => 'AI Cores Bundle',
            'priority' => 30,
            'role'     => 'runtime_registry_merge',
        ],
        'ai_social_media' => [
            'key'      => 'ai_social_media',
            'label'    => 'AiSocialMedia',
            'priority' => 40,
            'role'     => 'pulse_automation',
        ],
        'social_media_agent' => [
            'key'      => 'social_media_agent',
            'label'    => 'SocialMediaAgent',
            'priority' => 50,
            'role'     => 'agent_studio',
        ],
        'external_chatbot' => [
            'key'      => 'external_chatbot',
            'label'    => 'External Chatbot',
            'priority' => 60,
            'role'     => 'omni_channels',
        ],
        'ai_chat_pro' => [
            'key'      => 'ai_chat_pro',
            'label'    => 'AIChatPro',
            'priority' => 70,
            'role'     => 'omni_workspace',
        ],
        'chatbot_voice' => [
            'key'      => 'chatbot_voice',
            'label'    => 'ChatbotVoice',
            'priority' => 80,
            'role'     => 'omni_voice',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nexus Configuration
    |--------------------------------------------------------------------------
    */
    'nexus' => [
        'enabled_cores' => ['logi', 'creator', 'finance', 'micro', 'macro', 'entropy', 'equilibrium'],
        'authority_weights' => [
            'logi'        => 0.22,
            'creator'     => 0.08,
            'finance'     => 0.18,
            'micro'       => 0.12,
            'macro'       => 0.14,
            'entropy'     => 0.10,
            'equilibrium' => 0.16,
        ],
        'critique_rounds'       => 1,
        'round_robin_refinement' => true,
    ],
];
