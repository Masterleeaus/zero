<?php

return [
    'scheduler_enabled' => true,
    'process_limit' => 50,
    'allowlisted_fix_types' => ['metadata_update'],
    'allowlisted_target_tables' => ['titan_rewind_cases', 'tz_rewind_links', 'tz_rewind_conflicts'],
    'rewind_states' => [
        'initiated', 'signal-queued', 'awaiting-validation', 'validation-approved',
        'validation-rejected', 'awaiting-processing', 'processing', 'processed',
        'rewinding', 'awaiting-correction', 'rolled-back', 'conflict-hold', 'archived',
    ],
    'reissue_states' => ['held', 'ready-for-reissue', 'reissued'],
    'resolution_states' => ['open', 'resolved', 'rejected'],
    'permissions' => [
        'view'    => 'titanrewind.view',
        'manage'  => 'titanrewind.manage',
        'resolve' => 'titanrewind.resolve',
        'api'     => 'titanrewind.api',
    ],
    'menu' => [
        'enabled' => true,
        'route'   => 'titanrewind.cases.index',
        'label'   => 'Titan Rewind',
        'icon'    => 'tabler-history-toggle',
    ],
    'process_bridge' => [
        'process_table'              => 'tz_processes',
        'state_table'                => 'tz_process_states',
        'signal_table'               => 'tz_signals',
        'dependency_table'           => 'tz_process_dependencies',
        'allowed_rewind_from_states' => [
            'signal-queued', 'awaiting-validation', 'validation-approved',
            'validation-rejected', 'awaiting-processing', 'processing',
            'processed', 'processing-error', 'processing-hold',
        ],
        'rewind_state'      => 'rewinding',
        'rolled_back_state' => 'rolled-back',
        'correction_state'  => 'awaiting-correction',
    ],
    'signal_integration' => [
        'enabled'                     => true,
        'auto_initiate_from_signal'   => true,
        'rewind_trigger_types'        => ['process.rewind.requested', 'process.rollback.requested'],
        'rewind_trigger_states'       => ['processing-error', 'conflict-hold'],
        'root_signal_statuses'        => ['new', 'processing', 'processed'],
    ],
    'pulse' => [
        'enabled'              => true,
        'signal_type_prefix'   => 'rewind.',
        'queue_action_type'    => 'pulse.rollback.queued',
        'states' => [
            'initiated'             => 'rewind.initiated',
            'correction-submitted'  => 'rewind.correction_submitted',
            'completed'             => 'rewind.completed',
            'conflict'              => 'rewind.conflict',
        ],
    ],
    'notification_queue' => [
        'action_type'       => 'notification.queued',
        'dispatched_status' => 'dispatched',
    ],
    'domain_rules' => [
        'quotes'           => ['children' => ['service_jobs'], 'default_reuse' => true],
        'service_jobs'     => ['children' => ['checklists', 'invoices', 'service_issues'], 'default_reuse' => true],
        'checklists'       => ['children' => [], 'default_reuse' => true],
        'invoices'         => ['children' => ['payments'], 'default_reuse' => true],
        'payments'         => ['children' => [], 'default_reuse' => false, 'always_conflict' => 'external-transaction'],
        'sites'            => ['children' => ['service_jobs'], 'default_reuse' => true],
        'customer_details' => ['children' => ['sites', 'quotes'], 'default_reuse' => true],
        'service_issues'   => ['children' => [], 'default_reuse' => true],
    ],
];
