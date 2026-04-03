<?php

return [
    // Workflow statuses in order
    'workflow_statuses' => ['draft','review','approved','archived'],

    // Whether to auto-snapshot on status changes (implemented via DocumentWorkflowService)
    'auto_snapshot' => true,
];
