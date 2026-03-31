<?php

return [
    'vertical' => env('WORKCORE_VERTICAL', 'cleaning'),

    'labels' => [
        'sites'        => 'Jobs',
        'service_jobs' => 'Jobs',
        'service_job'  => 'Job',
        'checklists'   => 'Checklists',
    ],

    'features' => [
        'knowledgebase' => false,
        'noticeboard'   => false,
        'teamchat'      => false,
        'credit_notes'  => true,
    ],
];
