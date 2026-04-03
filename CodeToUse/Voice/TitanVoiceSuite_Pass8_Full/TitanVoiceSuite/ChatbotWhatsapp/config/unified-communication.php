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
        'persona_routing' => true,
        'offline_queue' => true,
    ],

    'features' => [
        'voicemail' => true,
        'callbacks' => true,
        'recording' => true,
        'intent_confirmation_threshold' => 0.60,
        'intent_execute_threshold' => 0.90,
    ],

    'ivr' => [
        'main_menu' => [
            'message' => 'Welcome. Press 1 for sales, 2 for support, 3 for billing, or 0 for an operator.',
            'options' => ['1' => 'sales', '2' => 'support', '3' => 'billing', '0' => 'operator'],
        ],
    ],

    'queue' => [
        'max_wait_offer_callback' => 300,
        'average_duration_minutes' => 5,
        'mock_queue_length' => 0,
        'mock_available_agents' => 1,
    ],

    'voicemail' => [
        'enabled' => true,
        'max_duration' => 600,
        'transcribe' => true,
    ],

    'callback' => [
        'enabled' => true,
        'max_future_days' => 7,
        'retry_attempts' => 3,
        'retry_interval' => 3600,
    ],

    'business_hours' => [
        'timezone' => config('app.timezone', 'UTC'),
        'default' => [
            'Monday-Friday' => ['open' => 9, 'close' => 17],
            'Saturday-Sunday' => null,
        ],
    ],

    'transfer' => [
        'default_number' => env('TWILIO_VOICE_TRANSFER_NUMBER'),
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
