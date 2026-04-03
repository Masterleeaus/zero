<?php

return [
    'name' => 'Inventory',
    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'timeout' => env('OPENAI_TIMEOUT', 30),
        ],
    ],

    ,'menu' => [
        'table' => env('INVENTORY_MENU_TABLE', 'menus')
    ]

    ,'sidebar' => [
        'include' => env('INVENTORY_SIDEBAR_INCLUDE', false)
    ]

    ,'reconciliation' => [
        'preview_enabled' => env('INVENTORY_RECON_PREVIEW', true),
        'permission' => env('INVENTORY_RECON_PERMISSION', 'inventory.reconcile'),
        'require_preview' => env('INVENTORY_RECON_REQUIRE_PREVIEW', false),
        'preview_ttl_minutes' => env('INVENTORY_RECON_PREVIEW_TTL', 15),
    ],
    'notifications' => [
        'enabled' => env('INVENTORY_NOTIF_ENABLED', false),
        'email_to' => env('INVENTORY_NOTIF_EMAIL_TO', ''),
        'webhook_url' => env('INVENTORY_NOTIF_WEBHOOK', ''),
    ]
];



