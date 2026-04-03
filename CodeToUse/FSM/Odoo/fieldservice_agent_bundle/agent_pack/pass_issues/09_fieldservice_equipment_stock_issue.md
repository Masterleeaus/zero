# Copilot Issue Prompt — fieldservice_equipment_stock

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
- `fieldservice_equipment_stock`

## Internal addon dependencies
- `fieldservice_stock`

## zero-main targets to inspect
- `app/Models/Work/Site.php`
- `app/Models/Work/ServiceJob.php`

## Odoo source hotspots
- `modules/fieldservice_equipment_stock/models/__init__.py`
- `modules/fieldservice_equipment_stock/models/fsm_equipment.py`
- `modules/fieldservice_equipment_stock/models/product_template.py`
- `modules/fieldservice_equipment_stock/models/stock_lot.py`
- `modules/fieldservice_equipment_stock/models/stock_move.py`
- `modules/fieldservice_equipment_stock/models/stock_picking_type.py`
- `modules/fieldservice_equipment_stock/security/ir.model.access.csv`
- `modules/fieldservice_equipment_stock/views/fsm_equipment.xml`
- `modules/fieldservice_equipment_stock/views/product_template.xml`
- `modules/fieldservice_equipment_stock/views/stock_lot.xml`
- `modules/fieldservice_equipment_stock/views/stock_picking_type.xml`
- `modules/fieldservice_equipment_stock/tests/__init__.py`
- `modules/fieldservice_equipment_stock/tests/test_fsm_equipment.py`
- `modules/fieldservice_equipment_stock/tests/test_stock_move.py`
- `modules/fieldservice_equipment_stock/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Integrate stock operations with your field service equipments

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes