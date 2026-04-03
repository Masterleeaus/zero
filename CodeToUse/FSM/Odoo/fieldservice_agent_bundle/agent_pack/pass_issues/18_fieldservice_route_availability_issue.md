# Copilot Issue Prompt — fieldservice_route_availability

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
- `fieldservice_route_availability`

## Internal addon dependencies
- `fieldservice_availability`

## zero-main targets to inspect
- `app/Services/Work`
- `app/Http/Controllers/Core/Work/ShiftController.php`

## Odoo source hotspots
- `modules/fieldservice_route_availability/models/__init__.py`
- `modules/fieldservice_route_availability/models/fsm_blackout_day.py`
- `modules/fieldservice_route_availability/models/fsm_order.py`
- `modules/fieldservice_route_availability/models/fsm_route.py`
- `modules/fieldservice_route_availability/views/fsm_blackout_day_templates.xml`
- `modules/fieldservice_route_availability/views/fsm_route.xml`
- `modules/fieldservice_route_availability/tests/__init__.py`
- `modules/fieldservice_route_availability/tests/test_route_availability.py`
- `modules/fieldservice_route_availability/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Restricts blackout days for Scheduled Start (ETA) orders with the same date.

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes