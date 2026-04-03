# Copilot Issue Prompt — fieldservice_sale_recurring

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
- `fieldservice_sale_recurring`

## Internal addon dependencies
- `fieldservice_recurring`
- `fieldservice_sale`
- `fieldservice_account`

## zero-main targets to inspect
- `app/Models/Work/ServiceAgreement.php`
- `app/Services/Work/AgreementSchedulerService.php`

## Odoo source hotspots
- `modules/fieldservice_sale_recurring/models/__init__.py`
- `modules/fieldservice_sale_recurring/models/fsm_recurring.py`
- `modules/fieldservice_sale_recurring/models/product_template.py`
- `modules/fieldservice_sale_recurring/models/sale_order.py`
- `modules/fieldservice_sale_recurring/models/sale_order_line.py`
- `modules/fieldservice_sale_recurring/security/ir.model.access.csv`
- `modules/fieldservice_sale_recurring/views/fsm_recurring.xml`
- `modules/fieldservice_sale_recurring/views/product_template.xml`
- `modules/fieldservice_sale_recurring/views/sale_order.xml`
- `modules/fieldservice_sale_recurring/tests/__init__.py`
- `modules/fieldservice_sale_recurring/tests/test_fsm_sale_recurring.py`
- `modules/fieldservice_sale_recurring/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Sell recurring field services.

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes