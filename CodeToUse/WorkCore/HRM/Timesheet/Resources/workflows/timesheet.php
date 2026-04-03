<?php

return [
    'module' => 'Timesheet',
    'events' => [
        [
            'key' => 'timesheet.entry.created',
            'label' => 'Timesheet entry created',
            'payload' => ['timesheet_id', 'user_id', 'company_id'],
        ],
        [
            'key' => 'timesheet.entry.updated',
            'label' => 'Timesheet entry updated',
            'payload' => ['timesheet_id', 'user_id', 'company_id'],
        ],
        [
            'key' => 'timesheet.timer.started',
            'label' => 'Timer started',
            'payload' => ['timer_id', 'user_id', 'company_id'],
        ],
        [
            'key' => 'timesheet.timer.stopped',
            'label' => 'Timer stopped',
            'payload' => ['timer_id', 'user_id', 'company_id'],
        ],
        [
            'key' => 'timesheet.week.submitted',
            'label' => 'Weekly timesheet submitted',
            'payload' => ['submission_id', 'user_id', 'company_id', 'week_start'],
        ],
        [
            'key' => 'timesheet.week.reviewed',
            'label' => 'Weekly timesheet approved/rejected',
            'payload' => ['submission_id', 'decision', 'approved_by'],
        ],
    ],
];
