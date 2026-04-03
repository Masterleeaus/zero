# Copilot Issue Prompt — fieldservice_account

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
- `fieldservice_account`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Money/InvoiceController.php`
- `app/Http/Controllers/Core/Money/PaymentController.php`

## Odoo source hotspots
- `modules/fieldservice_account/models/__init__.py`
- `modules/fieldservice_account/models/account_move.py`
- `modules/fieldservice_account/models/account_move_line.py`
- `modules/fieldservice_account/models/fsm_order.py`
- `modules/fieldservice_account/models/fsm_stage.py`
- `modules/fieldservice_account/security/ir.model.access.csv`
- `modules/fieldservice_account/views/account_move.xml`
- `modules/fieldservice_account/views/fsm_order.xml`
- `modules/fieldservice_account/views/fsm_stage.xml`
- `modules/fieldservice_account/tests/__init__.py`
- `modules/fieldservice_account/tests/test_fsm_account.py`
- `modules/fieldservice_account/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Track invoices linked to Field Service orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes