<?php

return [
    'sidebar' => [
        'label' => 'Titan Hello',
        'icon' => 'ti ti-phone',
        'items' => [
            ['label' => 'Dashboard', 'route' => 'titanhello.dashboard', 'icon' => 'ti ti-layout-dashboard'],
            ['label' => 'Call Inbox', 'route' => 'titanhello.calls.index', 'icon' => 'ti ti-phone-call'],
            ['label' => 'Dialer', 'route' => 'titanhello.calls.dialer', 'icon' => 'ti ti-phone-outgoing'],
            ['label' => 'Routing', 'route' => 'titanhello.routing.numbers.index', 'icon' => 'ti ti-route'],
            ['label' => 'Ring Groups', 'route' => 'titanhello.routing.ringgroups.index', 'icon' => 'ti ti-users'],
            ['label' => 'IVR Menus', 'route' => 'titanhello.routing.ivr.index', 'icon' => 'ti ti-forms'],
            ['label' => 'Dial Campaigns', 'route' => 'titanhello.campaigns.index', 'icon' => 'ti ti-broadcast'],
            ['label' => 'Settings', 'route' => 'titanhello.settings.index', 'icon' => 'ti ti-settings'],
            ['label' => 'Health', 'route' => 'titanhello.health', 'icon' => 'ti ti-heartbeat'],
        ],
    ],
];
