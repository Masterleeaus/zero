# Copilot Issue Prompt — fieldservice_calendar

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
- `fieldservice_calendar`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `app/Services/Work`
- `routes/core/work.routes.php`

## Odoo source hotspots
- `modules/fieldservice_calendar/models/__init__.py`
- `modules/fieldservice_calendar/models/calendar.py`
- `modules/fieldservice_calendar/models/fsm_order.py`
- `modules/fieldservice_calendar/models/fsm_team.py`
- `modules/fieldservice_calendar/views/fsm_order.xml`
- `modules/fieldservice_calendar/views/fsm_team.xml`
- `modules/fieldservice_calendar/tests/__init__.py`
- `modules/fieldservice_calendar/tests/test_fsm_calendar.py`
- `modules/fieldservice_calendar/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Add calendar to FSM Orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes