# Copilot Issue Prompt — fieldservice_stock

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
- `fieldservice_stock`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`

## Odoo source hotspots
- `modules/fieldservice_stock/models/__init__.py`
- `modules/fieldservice_stock/models/fsm_location.py`
- `modules/fieldservice_stock/models/fsm_order.py`
- `modules/fieldservice_stock/models/fsm_wizard.py`
- `modules/fieldservice_stock/models/procurement_group.py`
- `modules/fieldservice_stock/models/res_territory.py`
- `modules/fieldservice_stock/models/stock_move.py`
- `modules/fieldservice_stock/models/stock_picking.py`
- `modules/fieldservice_stock/models/stock_rule.py`
- `modules/fieldservice_stock/data/fsm_stock_data.xml`
- `modules/fieldservice_stock/security/ir.model.access.csv`
- `modules/fieldservice_stock/views/fsm_location.xml`
- `modules/fieldservice_stock/views/fsm_order.xml`
- `modules/fieldservice_stock/views/res_territory.xml`
- `modules/fieldservice_stock/views/stock.xml`
- `modules/fieldservice_stock/views/stock_picking.xml`
- `modules/fieldservice_stock/tests/__init__.py`
- `modules/fieldservice_stock/tests/test_fsm_stock.py`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Integrate the logistics operations with Field Service

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes