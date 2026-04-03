<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Module Configuration
    |--------------------------------------------------------------------------
    |
    | Controls for the Titan Admin module — routes, pagination defaults,
    | and audit log retention.
    |
    */

    'audit' => [
        'per_page'         => env('ADMIN_AUDIT_PER_PAGE', 50),
        'retention_days'   => env('ADMIN_AUDIT_RETENTION_DAYS', 365),
    ],

    'users' => [
        'per_page' => env('ADMIN_USERS_PER_PAGE', 25),
    ],

];
