<?php

return [
    'enabled' => env('OMNI_DUAL_WRITE_ENABLED', true),
    'drivers' => [
        'chatbot' => true,
        'whatsapp' => true,
        'telegram' => true,
        'messenger' => true,
        'voice' => true,
    ],
    'preserve_legacy_storage' => true,
    'fail_open' => true,
];
