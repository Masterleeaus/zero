<?php

return [
    'name' => 'ManagedPremises',

    // If enabled, show a quick "Create Job" link on site pages (only if the core Jobs/Projects module exists).
    'visit_generation' => [
        'default_days' => 30,
        'max_per_plan' => 60,
    ],

    'integrations' => [
        'jobs' => true,
        'documents' => true,
        'titan_zero' => false,
    ],
];
