# Copilot Issue Prompt — fieldservice_route

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
- `fieldservice_route`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/Shift.php`
- `app/Models/Work/ServiceJob.php`
- `app/Models/Team/Team.php`
- `app/Http/Controllers/Core/Work/ShiftController.php`
- `routes/core/work.routes.php`

## Odoo source hotspots
- `modules/fieldservice_route/models/__init__.py`
- `modules/fieldservice_route/models/fsm_location.py`
- `modules/fieldservice_route/models/fsm_order.py`
- `modules/fieldservice_route/models/fsm_route.py`
- `modules/fieldservice_route/models/fsm_route_day.py`
- `modules/fieldservice_route/models/fsm_route_dayroute.py`
- `modules/fieldservice_route/models/fsm_stage.py`
- `modules/fieldservice_route/data/fsm_route_day_data.xml`
- `modules/fieldservice_route/data/fsm_stage_data.xml`
- `modules/fieldservice_route/data/ir_sequence.xml`
- `modules/fieldservice_route/security/ir.model.access.csv`
- `modules/fieldservice_route/views/fsm_location.xml`
- `modules/fieldservice_route/views/fsm_order.xml`
- `modules/fieldservice_route/views/fsm_route.xml`
- `modules/fieldservice_route/views/fsm_route_day.xml`
- `modules/fieldservice_route/views/fsm_route_dayroute.xml`
- `modules/fieldservice_route/views/menu.xml`
- `modules/fieldservice_route/tests/__init__.py`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Organize the routes of each day.

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes