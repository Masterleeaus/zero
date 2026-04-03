# Copilot Issue Prompt — fieldservice_vehicle

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
- `fieldservice_vehicle`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Team/Team.php`
- `app/Models/Work/Shift.php`
- `app/Http/Controllers/Core/Work/ShiftController.php`

## Odoo source hotspots
- `modules/fieldservice_vehicle/models/__init__.py`
- `modules/fieldservice_vehicle/models/fsm_order.py`
- `modules/fieldservice_vehicle/models/fsm_person.py`
- `modules/fieldservice_vehicle/models/fsm_vehicle.py`
- `modules/fieldservice_vehicle/security/ir.model.access.csv`
- `modules/fieldservice_vehicle/security/res_groups.xml`
- `modules/fieldservice_vehicle/views/fsm_order.xml`
- `modules/fieldservice_vehicle/views/fsm_person.xml`
- `modules/fieldservice_vehicle/views/fsm_vehicle.xml`
- `modules/fieldservice_vehicle/views/menu.xml`
- `modules/fieldservice_vehicle/tests/__init__.py`
- `modules/fieldservice_vehicle/tests/test_fsm_vehicle.py`
- `modules/fieldservice_vehicle/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Manage Field Service vehicles and assign drivers

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes