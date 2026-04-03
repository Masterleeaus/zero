# Module Brief — fieldservice_timesheet

## Summary

Timesheet on Field Service Orders

## Pass order

29

## Internal addon dependencies

- `fieldservice_project`

## Likely zero-main targets

- `app/Models/Work/Timelog.php`
- `app/Http/Controllers/Core/Work/TimelogController.php`
- `app/Http/Controllers/Core/Team/WeeklyTimesheetController.php`
- `resources/views/default/panel/user/team/timesheets/*`

## Odoo source hotspots to inspect

- `modules/fieldservice_timesheet/models/__init__.py`
- `modules/fieldservice_timesheet/models/fsm_order.py`
- `modules/fieldservice_timesheet/models/hr_timesheet.py`
- `modules/fieldservice_timesheet/views/fsm_order.xml`
- `modules/fieldservice_timesheet/views/hr_timesheet.xml`
- `modules/fieldservice_timesheet/report/__init__.py`
- `modules/fieldservice_timesheet/report/report_timesheet_templates.xml`
- `modules/fieldservice_timesheet/report/timesheets_analysis_report.py`
- `modules/fieldservice_timesheet/README.rst`

## Merge strategy

- Port fields, statuses, workflows, and guardrails into existing Titan Zero domains.
- Do not port Odoo framework internals or generic security/runtime glue.
- Keep backend-first; only touch blades/APIs if needed to expose the new capability.
- Reuse company-scoped route/controller patterns already present in zero-main.

## Acceptance checklist

- host models extended or mapped cleanly
- no duplicate domain created
- routes/controllers wired only where needed
- migrations are additive and tenant-safe
- issue/PR notes record the exact zero-main files changed