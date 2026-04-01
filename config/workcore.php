<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WorkCore Vertical
    |--------------------------------------------------------------------------
    | The active vertical deployment context. Controls vocabulary displayed
    | throughout the UI. Options: 'cleaning', 'facilities', 'maintenance'
    */
    'vertical' => env('WORKCORE_VERTICAL', 'cleaning'),

    /*
    |--------------------------------------------------------------------------
    | WorkCore Feature Flags
    |--------------------------------------------------------------------------
    | Toggle features on/off per deployment. Disabling hides menu items and
    | prevents access to routes for unbuilt or deferred features.
    */
    'features' => [
        'crm'              => true,
        'work'             => true,
        'money'            => true,
        'team'             => true,
        'insights'         => true,
        'support'          => true,
        'knowledgebase'    => true,
        'notices'          => true,
        'teamchat'         => false,  // deferred — not yet tested
        'credit_notes'     => true,
        'bank_accounts'    => true,
        'expenses'         => true,
        'deals'            => false,  // future
        'follow_ups'       => true,
        'service_agreements' => true,
        'schedule_dispatch'  => true,
        'zones'            => true,
        'timelogs'         => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module 5 — Schedule Time Range Format (fieldservice_kanban_info)
    |--------------------------------------------------------------------------
    |
    | Controls how the schedule_time_range accessor formats dates/times on
    | service job kanban cards and list views.
    |
    | 'time_only'     — "15:30 - 17:00"  (default; compact for kanban)
    | 'date_and_time' — "27/04/2025 15:30 - 17:00"  (full date + time)
    |
    */
    'schedule_time_range_format' => env('WORKCORE_SCHEDULE_FORMAT', 'time_only'),
];

