<?php

return [
    'permissions' => [
        // Admin
        'titanhello.admin.view' => 'View Titan Hello admin/health',

        // Calls
        'titanhello.calls.view' => 'View Call Inbox and call details',
        'titanhello.calls.update' => 'Update calls (disposition, callback, notes)',
        'titanhello.calls.assign' => 'Assign calls to users',
        'titanhello.calls.callout' => 'Place outbound calls',

        
        // Routing / IVR
        'titanhello.routing.view' => 'View routing (numbers, ring groups, IVR)',
        'titanhello.routing.manage' => 'Manage routing (numbers, ring groups, IVR)',

        // Dial campaigns
        'titanhello.campaigns.view' => 'View dial campaigns',
        'titanhello.campaigns.manage' => 'Manage dial campaigns',
    'titanhello.callbacks.view' => 'View callbacks',
    'titanhello.callbacks.update' => 'Update callbacks',

        // Settings
        'titanhello.settings.manage' => 'Manage Titan Hello settings',
    ],
];
