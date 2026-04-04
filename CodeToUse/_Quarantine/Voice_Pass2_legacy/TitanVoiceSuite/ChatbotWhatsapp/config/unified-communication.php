<?php

return [
    'channels' => [
        'sms' => ['enabled' => true, 'format' => 'text'],
        'voice' => ['enabled' => true, 'format' => 'twiml'],
        'whatsapp' => ['enabled' => true, 'format' => 'text'],
    ],

    'routing' => [
        'voice_commands' => true,
        'ivr_menu' => true,
        'ai_fallback' => true,
        'agent_transfer' => true,
    ],

    'features' => [
        'voicemail' => true,
        'callbacks' => true,
        'recording' => true,
        'intent_confirmation_threshold' => 0.60,
        'intent_execute_threshold' => 0.90,
    ],

    'voice_commands' => [
        'enabled' => true,
        'confirmation_threshold' => 0.60,
        'execute_threshold' => 0.90,
        'fallback_to_ai' => true,
        'supported_intents' => [
            'create_ticket',
            'create_job',
            'list_tasks',
            'schedule_callback',
            'update_status',
        ],
    ],
];
