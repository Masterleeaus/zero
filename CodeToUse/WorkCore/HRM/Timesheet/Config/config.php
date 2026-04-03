<?php

return [
    'name' => 'Timesheet',

    /*
    |--------------------------------------------------------------------------
    | Integrations
    |--------------------------------------------------------------------------
    | This module is designed to integrate with Worksuite/Titan core tables
    | without hard dependencies. Adjust env values to match your core schema.
    */
    'integrations' => [
        // Core Tasks (preferred)
        'core_tasks_table' => env('TIMESHEET_CORE_TASKS_TABLE', 'tasks'),
        'core_tasks_title_column' => env('TIMESHEET_CORE_TASKS_TITLE_COLUMN', 'title'),
        'core_tasks_project_column' => env('TIMESHEET_CORE_TASKS_PROJECT_COLUMN', 'project_id'),

        // Core HR (rate-per-hour)
        'core_hrm_table' => env('TIMESHEET_CORE_HRM_TABLE', 'employees'),
        'core_hrm_user_column' => env('TIMESHEET_CORE_HRM_USER_COLUMN', 'user_id'),
        'core_hrm_rate_column' => env('TIMESHEET_CORE_HRM_RATE_COLUMN', 'rate_per_hour'),

        // Core Work Orders (optional)
        'core_work_orders_table' => env('TIMESHEET_CORE_WORK_ORDERS_TABLE', 'work_orders'),
        'core_work_orders_id_column' => env('TIMESHEET_CORE_WORK_ORDERS_ID_COLUMN', 'id'),
        'core_work_orders_title_column' => env('TIMESHEET_CORE_WORK_ORDERS_TITLE_COLUMN', 'title'),
        'core_work_orders_company_column' => env('TIMESHEET_CORE_WORK_ORDERS_COMPANY_COLUMN', 'company_id'),
        'core_work_orders_project_column' => env('TIMESHEET_CORE_WORK_ORDERS_PROJECT_COLUMN', 'project_id'),

        // Default multiplier used when entry is marked as overtime
        'default_overtime_multiplier' => (float) env('TIMESHEET_DEFAULT_OVERTIME_MULTIPLIER', 1.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Module behaviour
    |--------------------------------------------------------------------------
    */
    'features' => [
        // Costing: if enabled, the module will calculate fsm_cost_total using HR rate + overtime multiplier.
        'costing_enabled' => (bool) env('TIMESHEET_COSTING_ENABLED', true),

        // Timer (quick clock-on/off) stores draft runs and converts to a timesheet entry on stop.
        'timer_enabled' => (bool) env('TIMESHEET_TIMER_ENABLED', true),

        // Weekly submission/approval workflow for crews.
        'approvals_enabled' => (bool) env('TIMESHEET_APPROVALS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval workflow
    |--------------------------------------------------------------------------
    */
    'approvals' => [
        // Week starts on: 1=Mon ... 7=Sun (ISO-8601)
        'week_starts_on' => (int) env('TIMESHEET_WEEK_STARTS_ON', 1),
        // Optional: role name to allow approvals (e.g., 'crew_lead')
        'crew_lead_role' => env('TIMESHEET_CREW_LEAD_ROLE', null),
    ],
];
