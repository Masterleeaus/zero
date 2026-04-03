# Copilot Issue Prompt — fieldservice_repair_order_template

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
- `fieldservice_repair_order_template`

## Internal addon dependencies
- `fieldservice_repair`

## zero-main targets to inspect
- `repair workflow service`

## Odoo source hotspots
- `modules/fieldservice_repair_order_template/models/__init__.py`
- `modules/fieldservice_repair_order_template/models/fsm_order.py`
- `modules/fieldservice_repair_order_template/models/fsm_template.py`
- `modules/fieldservice_repair_order_template/views/fsm_template.xml`
- `modules/fieldservice_repair_order_template/tests/__init__.py`
- `modules/fieldservice_repair_order_template/tests/test_repair_order_template.py`
- `modules/fieldservice_repair_order_template/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Use Repair Order Templates when creating a repair orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes