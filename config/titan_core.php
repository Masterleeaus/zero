<?php

return [
    'enabled' => true,
    'ui_surface' => 'resources/views/default/panel/user/business-suite',
    'ai' => [
        'default_runtime' => env('TITAN_CORE_DEFAULT_RUNTIME', 'null'),
        'model_router' => env('TITAN_CORE_MODEL_ROUTER', 'zero'),
        'minimum_confidence' => (float) env('TITAN_CORE_MINIMUM_CONFIDENCE', 0.7),
    ],
    'knowledge' => [
        'mode' => env('TITAN_CORE_KNOWLEDGE_MODE', 'deferred'),
        'default_scope' => env('TITAN_CORE_KNOWLEDGE_SCOPE', 'tenant'),
    ],
    'sources' => [
        'zero_main' => [
            'key' => 'zero_main',
            'label' => 'Host Current Site',
            'priority' => 10,
            'role' => 'canonical_host',
        ],
        'titan_ai_residual' => [
            'key' => 'titan_ai_residual',
            'label' => 'Titan AI Residual Core',
            'priority' => 20,
            'role' => 'ai_knowledge_memory',
        ],
        'ai_cores' => [
            'key' => 'ai_cores',
            'label' => 'AI Cores Bundle',
            'priority' => 30,
            'role' => 'runtime_registry_merge',
        ],
        'ai_social_media' => [
            'key' => 'ai_social_media',
            'label' => 'AiSocialMedia',
            'priority' => 40,
            'role' => 'pulse_automation',
        ],
        'social_media_agent' => [
            'key' => 'social_media_agent',
            'label' => 'SocialMediaAgent',
            'priority' => 50,
            'role' => 'agent_studio',
        ],
        'external_chatbot' => [
            'key' => 'external_chatbot',
            'label' => 'External Chatbot',
            'priority' => 60,
            'role' => 'omni_channels',
        ],
        'ai_chat_pro' => [
            'key' => 'ai_chat_pro',
            'label' => 'AIChatPro',
            'priority' => 70,
            'role' => 'omni_workspace',
        ],
        'chatbot_voice' => [
            'key' => 'chatbot_voice',
            'label' => 'ChatbotVoice',
            'priority' => 80,
            'role' => 'omni_voice',
        ],
    ],
    'nexus' => [
        'enabled_cores' => ['logi', 'creator', 'finance', 'micro', 'macro', 'entropy', 'equilibrium'],
        'authority_weights' => [
            'logi' => 0.22,
            'creator' => 0.08,
            'finance' => 0.18,
            'micro' => 0.12,
            'macro' => 0.14,
            'entropy' => 0.10,
            'equilibrium' => 0.16,
        ],
        'critique_rounds' => 1,
        'round_robin_refinement' => true,
    ],
];
