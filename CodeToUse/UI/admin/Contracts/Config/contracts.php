<?php

return [
    'brand' => [
        'company_name' => env('CONTRACTS_BRAND_NAME', 'Your Company'),
        'logo_url' => env('CONTRACTS_BRAND_LOGO', ''),
        'footer_note' => env('CONTRACTS_BRAND_FOOTER', 'Digitally signed contracts are legally binding in many jurisdictions.'),
    ],
    'public_link_days' => env('CONTRACTS_PUBLIC_LINK_DAYS', 30),
    'webhook' => [
        'event_url' => env('CONTRACTS_WEBHOOK_URL', ''),
        'secret' => env('CONTRACTS_WEBHOOK_SECRET', ''),
    ],
    'reminders' => [
        'days_before_expiry' => env('CONTRACTS_REMIND_DAYS', 7),
    ],
];
