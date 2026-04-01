<?php

return [
    'vertical' => env('WORKCORE_VERTICAL', 'cleaning'),

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

    'features' => [
        'knowledgebase' => false,
        'noticeboard'   => false,
        'teamchat'      => false,
        'credit_notes'  => true,
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

