# Copilot Task: Phase 13 — Language File Normalisation

## Context
WorkCore merge Phase 13. All user-visible strings in the absorbed views must use the `__()` helper with normalised vocabulary. Domain-specific language files need creating, and the vertical language resolver must be wired so vocabulary can shift per deployment context.

## Target Vocabulary (from WorkCore cleaning vertical)
| Internal term | Cleaning vertical display |
|--------------|--------------------------|
| customers | Customers |
| customer | Customer |
| sites | Jobs |
| site | Job |
| service_jobs | Cleaning Checklist |
| service_job | Checklist Item |
| enquiries | Enquiries |
| enquiry | Enquiry |
| cleaners | Cleaners |
| cleaner | Cleaner |
| attendance | Shift Log |
| shifts | Availability |
| issues_support | Service Requests |
| knowledge_base | Playbooks |
| service_agreements | Service Agreements |
| team_chat | Team Chat |
| calendar | Schedule & Dispatch |
| insights | Insights |
| follow_up | Follow-Up |
| time_on_job | Time on Job |

## Your Task

### 1. Create domain language files
Create the following in `lang/en/`:

**`lang/en/crm.php`**:
```php
return [
    'customers' => 'Customers',
    'customer' => 'Customer',
    'enquiries' => 'Enquiries',
    'enquiry' => 'Enquiry',
    'deals' => 'Deals',
    'deal' => 'Deal',
    'contacts' => 'Contacts',
    'contact' => 'Contact',
    'follow_ups' => 'Follow-Ups',
    'follow_up' => 'Follow-Up',
    'convert_to_quote' => 'Convert to Quote',
    'customer_documents' => 'Documents',
    'customer_notes' => 'Notes',
];
```

**`lang/en/work.php`**:
```php
return [
    'sites' => 'Jobs',
    'site' => 'Job',
    'service_jobs' => 'Cleaning Checklist',
    'service_job' => 'Checklist Item',
    'checklists' => 'Checklists',
    'checklist' => 'Checklist',
    'attendance' => 'Shift Log',
    'shifts' => 'Availability',
    'leaves' => 'Leave',
    'timelogs' => 'Time on Job',
    'timelog' => 'Time Entry',
    'service_agreements' => 'Service Agreements',
    'service_agreement' => 'Service Agreement',
    'schedule_dispatch' => 'Schedule & Dispatch',
    'zones' => 'Zones',
    'zone' => 'Zone',
    'assign_job' => 'Assign Job',
    'checkin' => 'Check In',
    'checkout' => 'Check Out',
];
```

**`lang/en/money.php`**:
```php
return [
    'quotes' => 'Quotes',
    'quote' => 'Quote',
    'invoices' => 'Invoices',
    'invoice' => 'Invoice',
    'payments' => 'Payments',
    'payment' => 'Payment',
    'expenses' => 'Expenses',
    'expense' => 'Expense',
    'credit_notes' => 'Credit Notes',
    'credit_note' => 'Credit Note',
    'bank_accounts' => 'Bank Accounts',
    'bank_account' => 'Bank Account',
    'taxes' => 'Taxes',
    'tax' => 'Tax',
    'outstanding_balance' => 'Outstanding Balance',
    'overdue' => 'Overdue',
    'mark_paid' => 'Mark Paid',
    'convert_to_invoice' => 'Convert to Invoice',
];
```

**`lang/en/team.php`**:
```php
return [
    'cleaners' => 'Cleaners',
    'cleaner' => 'Cleaner',
    'roles' => 'Roles',
    'role' => 'Role',
    'teams' => 'Teams',
    'team' => 'Team',
    'availability' => 'Availability',
    'shift_log' => 'Shift Log',
    'timesheets' => 'Timesheets',
    'timesheet' => 'Timesheet',
    'emergency_contact' => 'Emergency Contact',
    'hire_date' => 'Hire Date',
];
```

**`lang/en/support.php`**:
```php
return [
    'tickets' => 'Service Requests',
    'ticket' => 'Service Request',
    'playbooks' => 'Playbooks',
    'playbook' => 'Playbook',
    'notices' => 'Notices',
    'notice' => 'Notice',
    'team_chat' => 'Team Chat',
    'knowledge_base' => 'Playbooks',
    'open' => 'Open',
    'resolved' => 'Resolved',
    'escalated' => 'Escalated',
    'waiting_on_team' => 'Waiting on Team',
    'waiting_on_user' => 'Waiting on Customer',
];
```

**`lang/en/insights.php`**:
```php
return [
    'insights' => 'Insights',
    'overview' => 'Overview',
    'reports' => 'Reports',
    'revenue' => 'Revenue',
    'outstanding' => 'Outstanding',
    'overdue_invoices' => 'Overdue Invoices',
    'jobs_scheduled' => 'Jobs Scheduled',
    'jobs_completed' => 'Jobs Completed',
    'cleaner_utilisation' => 'Cleaner Utilisation',
    'quote_conversion' => 'Quote Conversion',
    'customer_lifetime_value' => 'Customer Lifetime Value',
];
```

### 2. Update all absorbed views to use lang keys
Scan `resources/views/default/panel/user/` for hardcoded English strings in:
- Page titles (`<h1>`, `<h2>`)
- Button labels
- Table column headers
- Flash/status messages

Replace with `__('crm.customers')`, `__('work.sites')`, etc. **OR** `workcore_label('sites')` for strings that should respond to vertical config.

### 3. Wire vertical resolver to Blade
Add a Blade directive in `AppServiceProvider::boot()`:
```php
Blade::directive('vertical', function ($key) {
    return "<?php echo workcore_label({$key}); ?>";
});
```

Usage in views: `@vertical('sites')` → outputs `Jobs` for cleaning vertical.

### 4. Create output doc
Create `docs/LANGUAGE_NORMALISATION_PLAN.md`:
- List of lang files created
- List of views updated
- Vocabulary mapping table (source term → normalised term → vertical display)
