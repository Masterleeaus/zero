<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Process Engine
    |--------------------------------------------------------------------------
    |
    | Configuration for the TitanCore process lifecycle engine.
    | Aligns with docs/titancore/21_LIFECYCLE_ENGINE_STATE_MACHINE.md
    | and docs/titancore/41_PROCESS_ENGINE_OVERVIEW.md
    |
    */

    'enabled' => true,

    'tenant_key' => 'company_id',

    'default_state' => 'initiated',

    /*
    |--------------------------------------------------------------------------
    | State Machine Transitions
    |--------------------------------------------------------------------------
    |
    | Canonical valid state transitions. Maps from_state => [allowed to_states].
    | Mirrors ProcessStateMachine::VALID — authoritative source remains the class.
    |
    */

    'transitions' => [
        'initiated' => ['signal-queued', 'cancelled'],
        'signal-queued' => ['awaiting-validation', 'cancelled'],
        'awaiting-validation' => ['validation-approved', 'validation-rejected', 'conflict-hold'],
        'validation-approved' => ['awaiting-processing', 'processing'],
        'awaiting-processing' => ['processing', 'processing-rejected', 'awaiting-approval'],
        'awaiting-approval' => ['processing', 'approval-rejected', 'awaiting-more-info'],
        'processing' => ['processed', 'processing-error', 'processing-hold'],
        'processed' => ['rewinding'],
        'validation-rejected' => ['initiated'],
        'approval-rejected' => ['initiated'],
        'rewinding' => ['processed', 'cancelled'],
        'cancelled' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Checkpoint States
    |--------------------------------------------------------------------------
    |
    | States that require human approval before further processing.
    |
    */

    'approval_states' => [
        'awaiting-approval',
        'awaiting-more-info',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rewind Eligibility
    |--------------------------------------------------------------------------
    |
    | States from which a process may be rewound.
    | Aligns with docs/titancore/19_REWIND_ENGINE_AUDIT_MODEL.md
    |
    */

    'rewind_eligible_states' => [
        'processed',
        'rewinding',
    ],

    /*
    |--------------------------------------------------------------------------
    | Signal Envelope Compatibility
    |--------------------------------------------------------------------------
    |
    | Signal envelope emitted on each state transition.
    |
    */

    'signal_on_transition' => true,

    'signal_type' => 'process.state-changed',

    /*
    |--------------------------------------------------------------------------
    | Audit Linkage
    |--------------------------------------------------------------------------
    |
    | Whether to write audit trail entries on every state transition.
    |
    */

    'audit_every_transition' => true,

    /*
    |--------------------------------------------------------------------------
    | Memory Snapshot on Completion
    |--------------------------------------------------------------------------
    |
    | Whether TitanMemoryService::snapshot() is called when a process reaches
    | the 'processed' state. Enables rewind-compatible memory checkpoints.
    |
    */

    'memory_snapshot_on_completion' => env('TITAN_PROCESS_MEMORY_SNAPSHOT', true),

];
