<?php

return {
    'module': 'payroll',
    'capabilities': [
        {
            'key': 'payroll.help.explain_page',
            'label': 'Payroll: Explain this page',
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
            'key': 'payroll.employee-salary.data',
            'label': 'Payroll: Data',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.data',
            'voice_phrases': [
                'data'
            ]
        },
        {
            'key': 'payroll.employee-salary.edit-salary',
            'label': 'Payroll: Edit Salary',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.edit-salary',
            'voice_phrases': [
                'edit salary'
            ]
        },
        {
            'key': 'payroll.employee-salary.get-salary',
            'label': 'Payroll: Get Salary',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.get-salary',
            'voice_phrases': [
                'get salary'
            ]
        },
        {
            'key': 'payroll.employee-salary.get_update_salary',
            'label': 'Payroll: Get Update Salary',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.get_update_salary',
            'voice_phrases': [
                'get update salary'
            ]
        },
        {
            'key': 'payroll.employee-salary.increment',
            'label': 'Payroll: Increment',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.increment',
            'voice_phrases': [
                'increment'
            ]
        },
        {
            'key': 'payroll.employee-salary.increment-store',
            'label': 'Payroll: Increment Store',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.increment-store',
            'voice_phrases': [
                'increment store'
            ]
        },
        {
            'key': 'payroll.employee-salary.increment_edit',
            'label': 'Payroll: Increment Edit',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.increment_edit',
            'voice_phrases': [
                'increment edit'
            ]
        },
        {
            'key': 'payroll.employee-salary.increment_update',
            'label': 'Payroll: Increment Update',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.increment_update',
            'voice_phrases': [
                'increment update'
            ]
        },
        {
            'key': 'payroll.employee-salary.make-salary',
            'label': 'Payroll: Make Salary',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.make-salary',
            'voice_phrases': [
                'make salary'
            ]
        },
        {
            'key': 'payroll.employee-salary.payroll-cycle',
            'label': 'Payroll: Payroll Cycle',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.payroll-cycle',
            'voice_phrases': [
                'payroll cycle'
            ]
        },
        {
            'key': 'payroll.employee-salary.payroll-status',
            'label': 'Payroll: Payroll Status',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.payroll-status',
            'voice_phrases': [
                'payroll status'
            ]
        },
        {
            'key': 'payroll.employee-salary.update-salary',
            'label': 'Payroll: Update Salary',
            'risk': 'low',
            'requires': [],
            'handler': 'employee-salary.update-salary',
            'voice_phrases': [
                'update salary'
            ]
        },
        {
            'key': 'payroll.overtime-change-status',
            'label': 'Payroll: Overtime Change Status',
            'risk': 'low',
            'requires': [],
            'handler': 'overtime-change-status',
            'voice_phrases': [
                'overtime change status'
            ]
        },
        {
            'key': 'payroll.overtime-policies.employee-quick-action',
            'label': 'Payroll: Employee Quick Action',
            'risk': 'low',
            'requires': [],
            'handler': 'overtime-policies.employee-quick-action',
            'voice_phrases': [
                'employee quick action'
            ]
        },
        {
            'key': 'payroll.overtime-policy-remove',
            'label': 'Payroll: Overtime Policy Remove',
            'risk': 'low',
            'requires': [],
            'handler': 'overtime-policy-remove',
            'voice_phrases': [
                'overtime policy remove'
            ]
        },
        {
            'key': 'payroll.overtime-request-accept',
            'label': 'Payroll: Overtime Request Accept',
            'risk': 'low',
            'requires': [],
            'handler': 'overtime-request-accept',
            'voice_phrases': [
                'overtime request accept'
            ]
        },
        {
            'key': 'payroll.overtime-request-data',
            'label': 'Payroll: Overtime Request Data',
            'risk': 'low',
            'requires': [],
            'handler': 'overtime-request-data',
            'voice_phrases': [
                'overtime request data'
            ]
        },
        {
            'key': 'payroll.overtime-request-policy',
            'label': 'Payroll: Overtime Request Policy',
            'risk': 'low',
            'requires': [],
            'handler': 'overtime-request-policy',
            'voice_phrases': [
                'overtime request policy'
            ]
        },
        {
            'key': 'payroll.payroll-reports.export-report',
            'label': 'Payroll: Export Report',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll-reports.export-report',
            'voice_phrases': [
                'export report'
            ]
        },
        {
            'key': 'payroll.payroll-reports.fetch_tds',
            'label': 'Payroll: Fetch Tds',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll-reports.fetch_tds',
            'voice_phrases': [
                'fetch tds'
            ]
        },
        {
            'key': 'payroll.download_pdf',
            'label': 'Payroll: Download Pdf',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.download_pdf',
            'voice_phrases': [
                'download pdf'
            ]
        },
        {
            'key': 'payroll.generate_pay_slip',
            'label': 'Payroll: Generate Pay Slip',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.generate_pay_slip',
            'voice_phrases': [
                'generate pay slip'
            ]
        },
        {
            'key': 'payroll.get-cycle-data',
            'label': 'Payroll: Get Cycle Data',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.get-cycle-data',
            'voice_phrases': [
                'get cycle data'
            ]
        },
        {
            'key': 'payroll.get-employee',
            'label': 'Payroll: Get Employee',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.get-employee',
            'voice_phrases': [
                'get employee'
            ]
        },
        {
            'key': 'payroll.get_expense_title',
            'label': 'Payroll: Get Expense Title',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.get_expense_title',
            'voice_phrases': [
                'get expense title'
            ]
        },
        {
            'key': 'payroll.get_status',
            'label': 'Payroll: Get Status',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.get_status',
            'voice_phrases': [
                'get status'
            ]
        },
        {
            'key': 'payroll.overtime_settings',
            'label': 'Payroll: Overtime Settings',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.overtime_settings',
            'voice_phrases': [
                'overtime settings'
            ]
        },
        {
            'key': 'payroll.payroll_settings',
            'label': 'Payroll: Payroll Settings',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.payroll_settings',
            'voice_phrases': [
                'payroll settings'
            ]
        },
        {
            'key': 'payroll.update_status',
            'label': 'Payroll: Update Status',
            'risk': 'low',
            'requires': [],
            'handler': 'payroll.update_status',
            'voice_phrases': [
                'update status'
            ]
        },
        {
            'key': 'payroll.salary_groups.manage_employee',
            'label': 'Payroll: Manage Employee',
            'risk': 'low',
            'requires': [],
            'handler': 'salary_groups.manage_employee',
            'voice_phrases': [
                'manage employee'
            ]
        },
        {
            'key': 'payroll.salary_tds.get_status',
            'label': 'Payroll: Get Status',
            'risk': 'low',
            'requires': [],
            'handler': 'salary_tds.get_status',
            'voice_phrases': [
                'get status'
            ]
        },
        {
            'key': 'payroll.salary_tds.status',
            'label': 'Payroll: Status',
            'risk': 'low',
            'requires': [],
            'handler': 'salary_tds.status',
            'voice_phrases': [
                'status'
            ]
        }
    ],
    'go_enabled': true,
    'zero_enabled': true
};
