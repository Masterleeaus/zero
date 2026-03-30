<?php

return [
    'name' => 'FacilityManagement',
    'features' => [
        'sites' => true,
        'buildings' => true,
        'units' => true,
        'unit_types' => true,
        'assets' => true,
        'inspections' => true,
        'documents' => true,
        'meters' => true,
        'meter_reads' => true,
        'occupancy' => true,
        'ai' => true,
    ],
    'menu' => [
        'group' => 'Facilities',
        'label' => 'Facilities',
        'icon'  => 'ri-building-4-line',
        'route' => 'facility.dashboard',
        'children' => [
            ['label' => 'Dashboard',  'route' => 'facility.dashboard'],
            ['label' => 'Sites',      'route' => 'facility.sites.index'],
            ['label' => 'Buildings',  'route' => 'facility.buildings.index'],
            ['label' => 'Units',      'route' => 'facility.units.index'],
            ['label' => 'Unit Types', 'route' => 'facility.unittypes.index'],
            ['label' => 'Assets',     'route' => 'facility.assets.index'],
            ['label' => 'Inspections','route' => 'facility.inspections.index'],
            ['label' => 'Documents',  'route' => 'facility.docs.index'],
            ['label' => 'Meters',     'route' => 'facility.meters.index'],
            ['label' => 'Reads',      'route' => 'facility.reads.index'],
            ['label' => 'Occupancy',  'route' => 'facility.occupancy.index'],
        ]
    ],
'notify' => [
    'doc_expiry_days' => 30,
    'inspection_overdue_hours' => 24,
    'notify_user_id' => null, // set a user id or override in your app
],
'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'openai' => [
            'api_key'   => env('OPENAI_API_KEY', ''),
            'base_url'  => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model'     => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'fallback' => ['enabled' => true],
    ],
];
