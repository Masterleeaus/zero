<?php

return [
    'module' => 'workflow',
    'capabilities' => [
        [
            'key' => 'workflow.help.explain_page',
            'label' => 'Workflow: Explain this page',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'titanzero.intent.explain_page',
            'voice_phrases' => [
                'what is this page',
                'explain this',
                'help me'
            ]
        ],
        [
            'key' => 'workflow.index.alt2',
            'label' => 'Workflow: Alt2',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'index.alt2',
            'voice_phrases' => [
                'alt2'
            ]
        ],
        [
            'key' => 'workflow.',
            'label' => 'Workflow: ',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.',
            'voice_phrases' => [
                ''
            ]
        ],
        [
            'key' => 'workflow.check',
            'label' => 'Workflow: Check',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.check',
            'voice_phrases' => [
                'check'
            ]
        ],
        [
            'key' => 'workflow.index',
            'label' => 'Workflow: Index',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.index',
            'voice_phrases' => [
                'index'
            ]
        ],
        [
            'key' => 'workflow.reports',
            'label' => 'Workflow: Reports',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.reports',
            'voice_phrases' => [
                'reports'
            ]
        ],
        [
            'key' => 'workflow.reports.export.csv',
            'label' => 'Workflow: Csv',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.reports.export.csv',
            'voice_phrases' => [
                'csv'
            ]
        ],
        [
            'key' => 'workflow.run',
            'label' => 'Workflow: Run',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.run',
            'voice_phrases' => [
                'run'
            ]
        ],
        [
            'key' => 'workflow.settings',
            'label' => 'Workflow: Settings',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.settings',
            'voice_phrases' => [
                'settings'
            ]
        ],
        [
            'key' => 'workflow.settings.api',
            'label' => 'Workflow: Api',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.settings.api',
            'voice_phrases' => [
                'api'
            ]
        ],
        [
            'key' => 'workflow.settings.update',
            'label' => 'Workflow: Update',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.settings.update',
            'voice_phrases' => [
                'update'
            ]
        ],
        [
            'key' => 'workflow.settings.update.api',
            'label' => 'Workflow: Api',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.settings.update.api',
            'voice_phrases' => [
                'api'
            ]
        ],
        [
            'key' => 'workflow.timeline',
            'label' => 'Workflow: Timeline',
            'risk' => 'low',
            'requires' => [

            ],
            'handler' => 'workflow.timeline',
            'voice_phrases' => [
                'timeline'
            ]
        ]
    ],
    'go_enabled' => true,
    'zero_enabled' => true
];
