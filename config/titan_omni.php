<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Titan Omni — Unified Channel Orchestration Config
    |--------------------------------------------------------------------------
    */

    'enabled'         => env('TITAN_OMNI_ENABLED', false),
    'default_channel' => env('TITAN_OMNI_DEFAULT_CHANNEL', 'webchat'),

    /*
    |--------------------------------------------------------------------------
    | Voice Provider
    |--------------------------------------------------------------------------
    | Options: vapi | bland | twilio
    */
    'voice_provider'  => env('TITAN_OMNI_VOICE_PROVIDER', 'vapi'),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Driver
    |--------------------------------------------------------------------------
    | Options: twilio | meta
    */
    'whatsapp_driver' => env('TITAN_OMNI_WHATSAPP_DRIVER', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | Telegram
    |--------------------------------------------------------------------------
    */
    'telegram_token'  => env('TITAN_OMNI_TELEGRAM_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Bland.ai
    |--------------------------------------------------------------------------
    */
    'bland_api_key'   => env('BLAND_AI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | VAPI
    |--------------------------------------------------------------------------
    */
    'vapi_api_key'    => env('VAPI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Twilio
    |--------------------------------------------------------------------------
    */
    'twilio_sid'   => env('TWILIO_ACCOUNT_SID'),
    'twilio_token' => env('TWILIO_AUTH_TOKEN'),
    'twilio_from'  => env('TWILIO_FROM_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Meta (WhatsApp Cloud API + Facebook Messenger)
    |--------------------------------------------------------------------------
    */
    'meta_app_id'      => env('META_APP_ID'),
    'meta_app_secret'  => env('META_APP_SECRET'),
    'meta_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */
    'analytics_retention_days' => (int) env('TITAN_OMNI_ANALYTICS_RETENTION', 90),

    /*
    |--------------------------------------------------------------------------
    | Queue Names
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'dispatch'  => env('TITAN_OMNI_QUEUE_DISPATCH', 'omni'),
        'analytics' => env('TITAN_OMNI_QUEUE_ANALYTICS', 'omni-analytics'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Channels — per-channel enable flags and top-level settings
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'sms' => [
            'driver'          => 'sms',
            'enabled'         => env('OMNI_SMS_ENABLED', false),
            'retry_attempts'  => (int) env('OMNI_SMS_RETRY_ATTEMPTS', 3),
        ],
        'email' => [
            'driver'       => 'email',
            'enabled'      => env('OMNI_EMAIL_ENABLED', false),
            'from_address' => env('OMNI_EMAIL_FROM', env('MAIL_FROM_ADDRESS', '')),
            'from_name'    => env('OMNI_EMAIL_FROM_NAME', env('APP_NAME', 'Titan')),
        ],
        'whatsapp_meta' => [
            'driver'  => 'whatsapp_meta',
            'enabled' => env('OMNI_WHATSAPP_META_ENABLED', false),
        ],
        'whatsapp_twilio' => [
            'driver'  => 'whatsapp_twilio',
            'enabled' => env('OMNI_WHATSAPP_TWILIO_ENABLED', false),
        ],
        'telegram' => [
            'driver'  => 'telegram',
            'enabled' => env('OMNI_TELEGRAM_ENABLED', false),
        ],
        'webchat' => [
            'driver'  => 'webchat',
            'enabled' => env('OMNI_WEBCHAT_ENABLED', true),
        ],
        'voice' => [
            'driver'   => 'voice',
            'enabled'  => env('OMNI_VOICE_ENABLED', false),
            'provider' => env('TITAN_OMNI_VOICE_PROVIDER', 'vapi'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Drivers — credentials and config passed into each driver instance
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'sms' => [
            'sid'   => env('TWILIO_ACCOUNT_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from'  => env('TWILIO_FROM_NUMBER'),
        ],
        'email' => [
            'from_address' => env('OMNI_EMAIL_FROM', env('MAIL_FROM_ADDRESS', '')),
            'from_name'    => env('OMNI_EMAIL_FROM_NAME', env('APP_NAME', 'Titan')),
        ],
        'whatsapp_meta' => [
            'app_id'          => env('META_APP_ID'),
            'app_secret'      => env('META_APP_SECRET'),
            'verify_token'    => env('META_WEBHOOK_VERIFY_TOKEN'),
            'phone_number_id' => env('META_PHONE_NUMBER_ID'),
        ],
        'whatsapp_twilio' => [
            'sid'   => env('TWILIO_ACCOUNT_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from'  => env('TWILIO_WHATSAPP_FROM'),
        ],
        'telegram' => [
            'token' => env('TITAN_OMNI_TELEGRAM_TOKEN'),
        ],
        'webchat' => [],
        'voice' => [
            'provider'      => env('TITAN_OMNI_VOICE_PROVIDER', 'vapi'),
            'vapi_api_key'  => env('VAPI_API_KEY'),
            'bland_api_key' => env('BLAND_AI_API_KEY'),
        ],
    ],

];
