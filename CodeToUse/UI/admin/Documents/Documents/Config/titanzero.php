<?php

return [
    'module' => 'documents',
    'capabilities' => [
        [
            'key' => 'documents.help.explain_page',
            'label' => 'Documents: Explain this page',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'titanzero.intent.explain_page',
            'voice_phrases' => [
                'what is this page',
                'explain this',
                'help me',
            ],
        ],
        [
            'key' => 'documents.titanzero.ask',
            'label' => 'Documents: Ask Titan Zero',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'titan.zero.intent.run',
            'voice_phrases' => [
                'ask titan',
                'help with document',
                'draft scope',
            ],
        ],
        [
            'key' => 'documents.attachments.destroy',
            'label' => 'Documents: Destroy',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'attachments.destroy',
            'voice_phrases' => [
                'destroy',
            ],
        ],
        [
            'key' => 'documents.attachments.download',
            'label' => 'Documents: Download',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'attachments.download',
            'voice_phrases' => [
                'download',
            ],
        ],
        [
            'key' => 'documents.create',
            'label' => 'Documents: Create',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'create',
            'voice_phrases' => [
                'create',
            ],
        ],
        [
            'key' => 'documents.swms.index',
            'label' => 'Documents: Index',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'documents.swms.index',
            'voice_phrases' => [
                'index',
            ],
        ],
        [
            'key' => 'documents.general',
            'label' => 'Documents: General',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'general',
            'voice_phrases' => [
                'general',
            ],
        ],
        [
            'key' => 'documents.index',
            'label' => 'Documents: Index',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'index',
            'voice_phrases' => [
                'index',
            ],
        ],
        [
            'key' => 'documents.manager.dashboard',
            'label' => 'Documents: Dashboard',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'manager.dashboard',
            'voice_phrases' => [
                'dashboard',
            ],
        ],
        [
            'key' => 'documents.public.show',
            'label' => 'Documents: Show',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'public.show',
            'voice_phrases' => [
                'show',
            ],
        ],
        [
            'key' => 'documents.store',
            'label' => 'Documents: Store',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'store',
            'voice_phrases' => [
                'store',
            ],
        ],
        [
            'key' => 'documents.swms',
            'label' => 'Documents: Swms',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'swms',
            'voice_phrases' => [
                'swms',
            ],
        ],
        [
            'key' => 'documents.templates',
            'label' => 'Documents: Templates',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'templates',
            'voice_phrases' => [
                'templates',
            ],
        ],
        [
            'key' => 'documents.templates.apply',
            'label' => 'Documents: Apply',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'templates.apply',
            'voice_phrases' => [
                'apply',
            ],
        ],
        [
            'key' => 'documents.templates.print',
            'label' => 'Documents: Print',
            'risk' => 'low',
            'requires' => [],
            'handler' => 'templates.print',
            'voice_phrases' => [
                'print',
            ],
        ],
    ],
    'go_enabled' => true,
    'zero_enabled' => true,
];
