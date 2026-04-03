# Timesheet (Titan / Worksuite)

Tenant module for tracking labour time entries against Jobsites (projects), Job Tasks (tasks), and optional Work Orders.

## Key guarantees
- Tenant routes under `account/timesheets`
- No hard dependency on Taskly or Hrm modules
- Integration via table-based adapters for core Tasks + core HRM


## Pass 2 (2026-01-02)
- Added Timer (clock on/off) with conversion to timesheet entry
- Added Weekly submission + approvals inbox
- Added tenant settings page backed by timesheet_company_settings table
- Rewrote broken controller/views and hardened permissions
- Added integrations glue for core Tasks + core HR rate-based costing


## Pass 3 (2026-01-02)
- Added Reports (dashboard, by project/jobsite, by work order, by crew)
- Added Work Order provider integration + lookup endpoint
- Added report API endpoints (summary/breakdowns)
- Added DB indexes on timesheets for performance
- Added workflow manifest (Resources/workflows/timesheet_manifest.json)
