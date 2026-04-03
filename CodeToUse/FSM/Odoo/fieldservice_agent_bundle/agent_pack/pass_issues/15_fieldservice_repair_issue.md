# Copilot Issue Prompt — fieldservice_repair

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
- `fieldservice_repair`

## Internal addon dependencies
- `fieldservice_equipment_stock`

## zero-main targets to inspect
- `app/Models/Support or Work`
- `app/Http/Controllers/Core/Work`

## Odoo source hotspots
- `modules/fieldservice_repair/models/__init__.py`
- `modules/fieldservice_repair/models/fsm_order.py`
- `modules/fieldservice_repair/models/fsm_order_type.py`
- `modules/fieldservice_repair/models/repair_order.py`
- `modules/fieldservice_repair/data/fsm_order_type.xml`
- `modules/fieldservice_repair/views/fsm_order_view.xml`
- `modules/fieldservice_repair/migrations/18.0.2.0.0/post-migrate.py`
- `modules/fieldservice_repair/tests/__init__.py`
- `modules/fieldservice_repair/tests/test_fsm_repair.py`
- `modules/fieldservice_repair/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Integrate Field Service orders with MRP repair orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes