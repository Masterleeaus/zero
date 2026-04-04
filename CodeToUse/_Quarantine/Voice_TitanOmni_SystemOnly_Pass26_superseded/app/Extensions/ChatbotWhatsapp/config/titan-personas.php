<?php

return [
    'default' => 'nexus',
    'personas' => [
        'nexus' => [
            'label' => 'Titan Nexus',
            'channels' => ['whatsapp', 'sms', 'voice', 'embed'],
            'intents' => ['create_ticket', 'schedule_callback', 'general_support', 'booking_status'],
        ],
        'command' => [
            'label' => 'Titan Command',
            'channels' => ['voice', 'sms'],
            'intents' => ['create_job', 'list_tasks', 'update_status', 'view_report'],
        ],
        'go' => [
            'label' => 'Titan Go',
            'channels' => ['voice', 'sms'],
            'intents' => ['arrived_on_site', 'complete_checklist', 'update_status', 'create_job_note'],
        ],
    ],
];
