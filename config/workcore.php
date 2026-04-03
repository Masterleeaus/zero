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
    | WorkCore Vertical Labels
    |--------------------------------------------------------------------------
    | Maps internal snake_case keys to display labels for the active vertical.
    | Used by workcore_label() in app/Helpers/helpers.php.
    | These reflect the 'cleaning' vertical defaults; override via a subclass
    | or env-driven verticals config for other deployments.
    */
    'labels' => [
        'site'           => 'Job',
        'sites'          => 'Jobs',
        'service_job'    => 'Cleaning Checklist',
        'service_jobs'   => 'Cleaning Checklists',
        'checklist'      => 'Cleaning Checklist Item',
        'checklists'     => 'Cleaning Checklist Items',
        'attendance'     => 'Shift Log',
        'shift'          => 'Availability',
        'shifts'         => 'Availabilities',
        'issues_support' => 'Service Requests',
        'knowledge_base' => 'Playbooks',
    ],

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
        'deals'            => true,   // module 6 — fieldservice_crm
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

