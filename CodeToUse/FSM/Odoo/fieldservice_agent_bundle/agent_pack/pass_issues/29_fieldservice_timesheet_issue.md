# Copilot Issue Prompt — fieldservice_timesheet

You are integrating **one** Odoo Field Service addon into the existing **zero-main** Laravel host app.

## Hard rules
- full scan first: selected Odoo module + exact zero-main target files
- do not scaffold a new application/module
- do not copy Odoo runtime code directly
- extend existing Titan Zero domains first
- use `company_id` tenancy for new work
- keep routes in `routes/core/*.routes.php`
- keep controllers in `app/Http/Controllers/Core/*`
- keep models in `app/Models/*`
- keep views in `resources/views/default/panel/user/*`

## Module
- `fieldservice_timesheet`

## Internal addon dependencies
- `fieldservice_project`

## zero-main targets to inspect
- `app/Models/Work/Timelog.php`
- `app/Http/Controllers/Core/Work/TimelogController.php`
- `app/Http/Controllers/Core/Team/WeeklyTimesheetController.php`
- `resources/views/default/panel/user/team/timesheets/*`

## Odoo source hotspots
- `modules/fieldservice_timesheet/models/__init__.py`
- `modules/fieldservice_timesheet/models/fsm_order.py`
- `modules/fieldservice_timesheet/models/hr_timesheet.py`
- `modules/fieldservice_timesheet/views/fsm_order.xml`
- `modules/fieldservice_timesheet/views/hr_timesheet.xml`
- `modules/fieldservice_timesheet/report/__init__.py`
- `modules/fieldservice_timesheet/report/report_timesheet_templates.xml`
- `modules/fieldservice_timesheet/report/timesheets_analysis_report.py`
- `modules/fieldservice_timesheet/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Timesheet on Field Service Orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes