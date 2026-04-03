<?php

return [
    'default' => 'nexus',
    'lifecycle_map' => [
        'create_ticket' => 'support',
        'create_quote' => 'quote',
        'create_job' => 'planning',
        'update_schedule' => 'planning',
        'assign_technician' => 'dispatch',
        'list_tasks' => 'dispatch',
        'update_status' => 'completion',
        'close_job' => 'completion',
        'create_invoice' => 'invoice',
        'schedule_callback' => 'follow_up',
    ],
    'personas' => [
        'nexus' => [
            'label' => 'Titan Nexus',
            'channels' => ['whatsapp', 'sms', 'embed'],
            'stages' => ['support', 'quote', 'follow_up'],
            'intents' => ['create_ticket', 'create_quote', 'schedule_callback', 'general_support', 'booking_status'],
        ],
        'command' => [
            'label' => 'Titan Command',
            'channels' => ['voice', 'sms', 'whatsapp'],
            'stages' => ['planning', 'dispatch', 'invoice'],
            'intents' => ['create_job', 'update_schedule', 'assign_technician', 'list_tasks', 'create_invoice', 'view_report'],
            'conversation_name_contains' => 'Owner',
        ],
        'go' => [
            'label' => 'Titan Go',
            'channels' => ['voice', 'sms'],
            'stages' => ['dispatch', 'completion', 'field_work'],
            'intents' => ['assign_technician', 'arrived_on_site', 'complete_checklist', 'update_status', 'close_job', 'create_job_note'],
            'conversation_name_contains' => 'Field',
        ],
    ],
];
