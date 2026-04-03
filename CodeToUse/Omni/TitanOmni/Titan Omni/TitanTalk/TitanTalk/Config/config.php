<?php

return [

    'name'        => 'Titan Talk',
    'feature_key' => 'titan-talk',

    /*
    |--------------------------------------------------------------------------
    | Default provider & model for Titan Talk
    |--------------------------------------------------------------------------
    |
    | If these are null, Titan Core will use its own defaults
    | (AI_PROVIDER / AI_MODEL and per-company settings).
    |
    */

    'provider' => env('TITAN_TALK_PROVIDER', null),
    'model'    => env('TITAN_TALK_MODEL', null),

    /*
    |--------------------------------------------------------------------------
    | Channel-specific model overrides
    |--------------------------------------------------------------------------
    |
    | Use these to force certain channels onto a specific model. If null,
    | Titan Talk falls back to TITAN_TALK_MODEL, then Titan Core defaults.
    |
    */

    'channel_models' => [
        'web'       => env('TITAN_TALK_WEB_MODEL', null),
        'whatsapp'  => env('TITAN_TALK_WHATSAPP_MODEL', null),
        'telegram'  => env('TITAN_TALK_TELEGRAM_MODEL', null),
        'messenger' => env('TITAN_TALK_MESSENGER_MODEL', null),
        'voice'     => env('TITAN_TALK_VOICE_MODEL', null),
        'sms'       => env('TITAN_TALK_SMS_MODEL', null),
        'email'     => env('TITAN_TALK_EMAIL_MODEL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Policy profiles (assistant vs compliance, etc.)
    |--------------------------------------------------------------------------
    */

    'policy_profiles' => [
        'assistant' => [
            'description' => 'General-purpose helpful assistant for trades and service businesses.',
        ],
        'compliance' => [
            'description' => 'Strict compliance/safety-oriented responses (e.g. codes and standards).',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing rules (per channel defaults)
    |--------------------------------------------------------------------------
    */

    'routing' => [
        'defaults' => [
            'web'       => ['policy_profile' => 'assistant'],
            'whatsapp'  => ['policy_profile' => 'assistant'],
            'telegram'  => ['policy_profile' => 'assistant'],
            'messenger' => ['policy_profile' => 'assistant'],
            'voice'     => ['policy_profile' => 'assistant'],
            'sms'       => ['policy_profile' => 'assistant'],
            'email'     => ['policy_profile' => 'assistant'],
        ],
        'numbers' => [
            // e.g. 'whatsapp:+61XXXXXXXXX' => 'compliance',
        ],
    ],
];
