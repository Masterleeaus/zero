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
        'shifts'         => 'Availability',
        'issues_support' => 'Service Requests',
        'knowledge_base' => 'Playbooks',
    ],

    'features' => [
        'knowledgebase' => false,
        'noticeboard'   => false,
        'teamchat'      => false,
    ],
];
