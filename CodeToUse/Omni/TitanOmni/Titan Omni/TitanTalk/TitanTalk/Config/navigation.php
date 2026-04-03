<?php

return [
    'sidebar' => [
        'label' => 'TitanTalk',
        'icon' => 'ti ti-message-chatbot',
        'items' => [
            ['label' => 'Dashboard', 'route' => 'titantalk.dashboard', 'icon' => 'ti ti-layout-dashboard'],
            ['label' => 'Intents', 'route' => 'titantalk.intents.index', 'icon' => 'ti ti-bolt'],
            ['label' => 'Entities', 'route' => 'titantalk.entities.index', 'icon' => 'ti ti-atom'],
            ['label' => 'Training', 'route' => 'titantalk.training.index', 'icon' => 'ti ti-list-check'],
            ['label' => 'Settings', 'route' => 'titantalk.settings.index', 'icon' => 'ti ti-settings'],
        ],
    ],
];
