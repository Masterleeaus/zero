<?php

/**
 * Titan AI Model Routing Configuration
 *
 * Controls model selection per intent and provider fallback chain.
 * Managed via the Titan Core Admin Panel at /dashboard/admin/titan/core/models.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Default Models
    |--------------------------------------------------------------------------
    */
    'default_text_model'  => env('TITAN_DEFAULT_TEXT_MODEL', 'gpt-4o'),
    'default_image_model' => env('TITAN_DEFAULT_IMAGE_MODEL', 'dall-e-3'),

    /*
    |--------------------------------------------------------------------------
    | Intent-to-Model Mapping
    |--------------------------------------------------------------------------
    | Override which model handles each named intent.
    */
    'intents' => [
        'text.complete'    => env('TITAN_INTENT_TEXT_COMPLETE', 'gpt-4o'),
        'image.generate'   => env('TITAN_INTENT_IMAGE_GENERATE', 'dall-e-3'),
        'voice.synthesize' => env('TITAN_INTENT_VOICE_SYNTHESIZE', 'tts-1'),
        'agent.task'       => env('TITAN_INTENT_AGENT_TASK', 'gpt-4o'),
        'code.assist'      => env('TITAN_INTENT_CODE_ASSIST', 'gpt-4o'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Chain
    |--------------------------------------------------------------------------
    | Ordered list of providers to try if the primary fails.
    */
    'fallback_chain' => [
        'openai',
        'gemini',
        'deepseek',
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Availability
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'openai'   => ['key_env' => 'OPENAI_API_KEY',   'enabled' => true],
        'gemini'   => ['key_env' => 'GEMINI_API_KEY',   'enabled' => true],
        'deepseek' => ['key_env' => 'DEEPSEEK_API_KEY', 'enabled' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | BYO Key Support
    |--------------------------------------------------------------------------
    */
    'byo_key_enabled' => env('TITAN_BYO_KEY_ENABLED', false),

];
