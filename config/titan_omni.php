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

];
