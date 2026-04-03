<?php

return [
    'menu' => [
        'title' => 'Timesheets',
        'entries' => 'Entries',
        'timer' => 'Clock On/Off',
        'approvals' => 'Weekly Approvals',
        'reports' => 'Reports',
        'settings' => 'Settings',
    ],

    'actions' => [
        'add' => 'Add Entry',
        'edit' => 'Edit Entry',
        'save' => 'Save',
        'back' => 'Back',
        'view' => 'View',
        'export_csv' => 'Export CSV',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'actions' => 'Actions',
    ],

    'filters' => [
        'from' => 'From',
        'to' => 'To',
    ],

    'fields' => [
        'date' => 'Date',
        'user' => 'User',
        'project' => 'Jobsite (Project)',
        'task' => 'Job Task',
        'work_order' => 'Work Order',
        'work_order_none' => 'No Work Order',
        'work_order_selected' => 'Selected Work Order',
        'hours' => 'Hours',
        'minutes' => 'Minutes',
        'time' => 'Time',
        'type' => 'Type',
        'notes' => 'Notes',
        'cost' => 'Cost',
        'status' => 'Status',
        'started_at' => 'Started',
        'stopped_at' => 'Stopped',
        'submitted_at' => 'Submitted',
    ],

    'types' => [
        'regular' => 'Regular',
        'overtime' => 'Overtime',
    ],

    'hints' => [
        'project' => 'Enter the Jobsite/Project ID from core Jobsites/Projects.',
        'task' => 'Enter the core Task ID (or Taskly if enabled).',
        'work_order' => 'Enter the Work Order ID (optional).',
    ],

    'timer' => [
        'disabled' => 'Timer is disabled for this company.',
        'current' => 'Current Timer',
        'running' => 'Timer running',
        'no_running' => 'No timer is currently running.',
        'start' => 'Start',
        'stop' => 'Stop',
        'started' => 'Timer started.',
        'none_running' => 'No running timer found.',
        'stopped_converted' => 'Timer stopped and converted to a timesheet entry.',
        'recent' => 'Recent Timers',
    ],

    'approvals' => [
        'disabled' => 'Approvals are disabled for this company.',
        'my_week' => 'My Week',
        'week' => 'Week',
        'submit' => 'Submit for Approval',
        'submitted' => 'Timesheet submitted for approval.',
        'already_submitted' => 'This week has already been submitted.',
        'already_approved' => 'This week has been approved.',
        'was_rejected' => 'This week was rejected. You can adjust entries and resubmit.',
        'inbox' => 'Approvals Inbox',
        'review' => 'Review Submission',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'approved' => 'Submission approved.',
        'rejected' => 'Submission rejected.',
        'not_submitted' => 'This submission is not in a submitted state.',
        'submitter_notes' => 'Notes to approver',
        'approver_notes' => 'Notes (optional)',
    ],

    'settings' => [
        'saved' => 'Settings saved.',
        'costing_enabled' => 'Enable costing (rate × time)',
        'timer_enabled' => 'Enable clock on/off timer',
        'approvals_enabled' => 'Enable weekly submission/approval',
    ],

    'msg' => [
        'created' => 'Timesheet entry created.',
        'updated' => 'Timesheet entry updated.',
        'deleted' => 'Timesheet entry deleted.',
    ],

    'confirm' => [
        'delete' => 'Delete this entry?',
    ],

    'empty' => 'No records found.',

    'reports' => [
        'title' => 'Timesheet Reports',
        'from' => 'From',
        'to' => 'To',
        'apply' => 'Apply',
        'range' => 'Range',
        'entries' => 'Entries',
        'total_hours' => 'Total Hours',
        'total_cost' => 'Total Cost',
        'by_project' => 'By Jobsite',
        'by_work_order' => 'By Work Order',
        'by_crew' => 'By Crew',
        'user' => 'User',
        'view_all' => 'View all',
        'item' => 'Item',
        'none' => 'No data for this range.',
    ],

];
