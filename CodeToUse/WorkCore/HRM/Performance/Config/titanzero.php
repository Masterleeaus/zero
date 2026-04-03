<?php

return {
    'module': 'performance',
    'capabilities': [
        {
            'key': 'performance.help.explain_page',
            'label': 'Performance: Explain this page',
            'risk': 'low',
            'requires': [],
            'handler': 'titanzero.intent.explain_page',
            'voice_phrases': [
                'what is this page',
                'explain this',
                'help me'
            ]
        },
        {
            'key': 'performance.action.mark_as_actioned',
            'label': 'Performance: Mark As Actioned',
            'risk': 'low',
            'requires': [],
            'handler': 'action.mark_as_actioned',
            'voice_phrases': [
                'mark as actioned'
            ]
        },
        {
            'key': 'performance.agenda.mark_as_discussed',
            'label': 'Performance: Mark As Discussed',
            'risk': 'low',
            'requires': [],
            'handler': 'agenda.mark_as_discussed',
            'voice_phrases': [
                'mark as discussed'
            ]
        },
        {
            'key': 'performance.api.',
            'label': 'Performance: ',
            'risk': 'low',
            'requires': [],
            'handler': 'api.',
            'voice_phrases': [
                ''
            ]
        },
        {
            'key': 'performance.job-performance.index',
            'label': 'Performance: Index',
            'risk': 'low',
            'requires': [],
            'handler': 'job-performance.index',
            'voice_phrases': [
                'index'
            ]
        },
        {
            'key': 'performance.job-performance.rescore',
            'label': 'Performance: Rescore',
            'risk': 'low',
            'requires': [],
            'handler': 'job-performance.rescore',
            'voice_phrases': [
                'rescore'
            ]
        },
        {
            'key': 'performance.job-performance.show',
            'label': 'Performance: Show',
            'risk': 'low',
            'requires': [],
            'handler': 'job-performance.show',
            'voice_phrases': [
                'show'
            ]
        },
        {
            'key': 'performance.job-performance.signoff',
            'label': 'Performance: Signoff',
            'risk': 'low',
            'requires': [],
            'handler': 'job-performance.signoff',
            'voice_phrases': [
                'signoff'
            ]
        },
        {
            'key': 'performance.key-results.send-reminder',
            'label': 'Performance: Send Reminder',
            'risk': 'low',
            'requires': [],
            'handler': 'key-results.send-reminder',
            'voice_phrases': [
                'send reminder'
            ]
        },
        {
            'key': 'performance.key-results.show-description',
            'label': 'Performance: Show Description',
            'risk': 'low',
            'requires': [],
            'handler': 'key-results.show-description',
            'voice_phrases': [
                'show description'
            ]
        },
        {
            'key': 'performance.meetings.calendar_view',
            'label': 'Performance: Calendar View',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.calendar_view',
            'voice_phrases': [
                'calendar view'
            ]
        },
        {
            'key': 'performance.meetings.load_more',
            'label': 'Performance: Load More',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.load_more',
            'voice_phrases': [
                'load more'
            ]
        },
        {
            'key': 'performance.meetings.load_more_past',
            'label': 'Performance: Load More Past',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.load_more_past',
            'voice_phrases': [
                'load more past'
            ]
        },
        {
            'key': 'performance.meetings.mark_as_cancelled',
            'label': 'Performance: Mark As Cancelled',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.mark_as_cancelled',
            'voice_phrases': [
                'mark as cancelled'
            ]
        },
        {
            'key': 'performance.meetings.mark_as_complete',
            'label': 'Performance: Mark As Complete',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.mark_as_complete',
            'voice_phrases': [
                'mark as complete'
            ]
        },
        {
            'key': 'performance.meetings.monthly_on',
            'label': 'Performance: Monthly On',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.monthly_on',
            'voice_phrases': [
                'monthly on'
            ]
        },
        {
            'key': 'performance.meetings.send_reminder',
            'label': 'Performance: Send Reminder',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.send_reminder',
            'voice_phrases': [
                'send reminder'
            ]
        },
        {
            'key': 'performance.meetings.view_meeting_list',
            'label': 'Performance: View Meeting List',
            'risk': 'low',
            'requires': [],
            'handler': 'meetings.view_meeting_list',
            'voice_phrases': [
                'view meeting list'
            ]
        },
        {
            'key': 'performance.objectives.show-description',
            'label': 'Performance: Show Description',
            'risk': 'low',
            'requires': [],
            'handler': 'objectives.show-description',
            'voice_phrases': [
                'show description'
            ]
        },
        {
            'key': 'performance.okr-scoring.export-report',
            'label': 'Performance: Export Report',
            'risk': 'low',
            'requires': [],
            'handler': 'okr-scoring.export-report',
            'voice_phrases': [
                'export report'
            ]
        },
        {
            'key': 'performance.performance',
            'label': 'Performance: Performance',
            'risk': 'low',
            'requires': [],
            'handler': 'performance',
            'voice_phrases': [
                'performance'
            ]
        },
        {
            'key': 'performance.performance-dashboard.chart',
            'label': 'Performance: Chart',
            'risk': 'low',
            'requires': [],
            'handler': 'performance-dashboard.chart',
            'voice_phrases': [
                'chart'
            ]
        },
        {
            'key': 'performance.performance-settings.meeting-setting',
            'label': 'Performance: Meeting Setting',
            'risk': 'low',
            'requires': [],
            'handler': 'performance-settings.meeting-setting',
            'voice_phrases': [
                'meeting setting'
            ]
        },
        {
            'key': 'performance.reports.callback_trends',
            'label': 'Performance: Callback Trends',
            'risk': 'low',
            'requires': [],
            'handler': 'reports.callback_trends',
            'voice_phrases': [
                'callback trends'
            ]
        },
        {
            'key': 'performance.reports.export.callback_trends_csv',
            'label': 'Performance: Callback Trends Csv',
            'risk': 'low',
            'requires': [],
            'handler': 'reports.export.callback_trends_csv',
            'voice_phrases': [
                'callback trends csv'
            ]
        },
        {
            'key': 'performance.reports.export.job_performance_csv',
            'label': 'Performance: Job Performance Csv',
            'risk': 'low',
            'requires': [],
            'handler': 'reports.export.job_performance_csv',
            'voice_phrases': [
                'job performance csv'
            ]
        },
        {
            'key': 'performance.reports.job_performance',
            'label': 'Performance: Job Performance',
            'risk': 'low',
            'requires': [],
            'handler': 'reports.job_performance',
            'voice_phrases': [
                'job performance'
            ]
        },
        {
            'key': 'performance.reports.safety_risk',
            'label': 'Performance: Safety Risk',
            'risk': 'low',
            'requires': [],
            'handler': 'reports.safety_risk',
            'voice_phrases': [
                'safety risk'
            ]
        },
        {
            'key': 'performance.reports.site_performance',
            'label': 'Performance: Site Performance',
            'risk': 'low',
            'requires': [],
            'handler': 'reports.site_performance',
            'voice_phrases': [
                'site performance'
            ]
        }
    ],
    'go_enabled': true,
    'zero_enabled': true
};
