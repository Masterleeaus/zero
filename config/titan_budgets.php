<?php

/**
 * Titan Token Budget Configuration
 *
 * Controls per-user, per-company, and per-intent token spending limits.
 * Managed via the Titan Core Admin Panel at /dashboard/admin/titan/core/budgets.
 *
 * All values are in tokens unless otherwise noted.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Daily Platform Limit
    |--------------------------------------------------------------------------
    | Maximum total tokens the platform may spend in a 24-hour window.
    | Set to 0 to disable the platform-wide limit.
    */
    'daily_limit' => (int) env('TITAN_AI_DAILY_LIMIT', 0),

    /*
    |--------------------------------------------------------------------------
    | Per-Request Maximum
    |--------------------------------------------------------------------------
    | Hard cap on tokens consumed by a single AI request.
    */
    'per_request_max' => (int) env('TITAN_AI_PER_REQUEST_LIMIT', 4096),

    /*
    |--------------------------------------------------------------------------
    | Per-User Daily Cap
    |--------------------------------------------------------------------------
    | Maximum tokens a single user may consume per calendar day.
    | Set to 0 to use the plan-level default.
    */
    'per_user_daily' => (int) env('TITAN_USER_DAILY_LIMIT', 0),

    /*
    |--------------------------------------------------------------------------
    | Per-Company Daily Cap
    |--------------------------------------------------------------------------
    | Maximum tokens a single company/tenant may consume per calendar day.
    */
    'per_company_daily' => (int) env('TITAN_COMPANY_DAILY_LIMIT', 0),

    /*
    |--------------------------------------------------------------------------
    | Per-Intent Token Caps
    |--------------------------------------------------------------------------
    */
    'intents' => [
        'text.complete'    => (int) env('TITAN_BUDGET_TEXT_COMPLETE', 0),
        'image.generate'   => (int) env('TITAN_BUDGET_IMAGE_GENERATE', 0),
        'voice.synthesize' => (int) env('TITAN_BUDGET_VOICE_SYNTHESIZE', 0),
        'agent.task'       => (int) env('TITAN_BUDGET_AGENT_TASK', 0),
        'code.assist'      => (int) env('TITAN_BUDGET_CODE_ASSIST', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Triggers
    |--------------------------------------------------------------------------
    | When budget thresholds are reached, trigger these fallback actions.
    | Options: 'deny', 'fallback_model', 'notify_admin'
    */
    'on_budget_exceeded' => env('TITAN_BUDGET_EXCEEDED_ACTION', 'deny'),

];
