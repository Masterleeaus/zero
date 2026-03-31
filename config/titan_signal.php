<?php

return [
    'name' => 'Titan Signal',
    'route' => [
        'prefix' => 'dashboard/user/titan-signals',
        'name' => 'dashboard.user.titan-signals.',
        'middleware' => ['web', 'auth', 'updateUserActivity'],
    ],
    'api' => [
        'prefix' => 'api/signals',
    ],
    'tenant_key' => 'company_id',
    'crew_key' => 'team_id',
    'actor_key' => 'user_id',
    'supported_sources' => ['work', 'money', 'governance', 'automation', 'rewind'],
    'default_signal_status' => 'new',
    'default_process_state' => 'initiated',
    'approval_roles' => [
        'red' => ['manager'],
        'high_amount' => ['director'],
        'staff_no_show' => ['dispatch_lead'],
    ],
    'subscribers' => [
        'zero',
        'pulse',
        'rewind',
    ],
    'approval_thresholds' => [
        'amount_cents' => 100000,
    ],
    'registry' => [
        'job.completed' => [
            'domain' => 'jobs',
            'kind' => 'job',
            'required_payload_fields' => ['job_id', 'completed_at'],
            'allowed_process_states' => ['awaiting-processing', 'processing', 'processed'],
        ],
        'invoice.overdue' => [
            'domain' => 'invoices',
            'kind' => 'invoice',
            'required_payload_fields' => ['invoice_id', 'amount_cents'],
            'approval' => ['always' => true, 'roles' => ['director']],
        ],
        'quote.accepted' => [
            'domain' => 'quotes',
            'kind' => 'quote',
            'required_payload_fields' => ['quote_id', 'customer_id'],
        ],
        'staff.no_show' => [
            'domain' => 'jobs',
            'kind' => 'attendance',
            'required_payload_fields' => ['job_id', 'worker_id'],
            'approval' => ['always' => true, 'roles' => ['dispatch_lead']],
        ],
        'process.state-changed' => [
            'domain' => 'governance',
            'kind' => 'process',
            'required_payload_fields' => ['from_state', 'to_state'],
        ],
    ],
];
