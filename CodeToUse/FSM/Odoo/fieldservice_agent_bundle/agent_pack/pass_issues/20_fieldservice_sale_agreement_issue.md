# Copilot Issue Prompt — fieldservice_sale_agreement

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
- `fieldservice_sale_agreement`

## Internal addon dependencies
- `fieldservice_agreement`
- `fieldservice_sale`

## zero-main targets to inspect
- `app/Http/Controllers/Core/Money/QuoteController.php`
- `app/Models/Work/ServiceAgreement.php`

## Odoo source hotspots
- `modules/fieldservice_sale_agreement/models/__init__.py`
- `modules/fieldservice_sale_agreement/models/sale_order.py`
- `modules/fieldservice_sale_agreement/tests/__init__.py`
- `modules/fieldservice_sale_agreement/tests/test_fsm_sale_agreement.py`
- `modules/fieldservice_sale_agreement/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Integrate Field Service with Sale Agreements

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes