# Copilot Issue Prompt — fieldservice_agreement

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
- `fieldservice_agreement`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/ServiceAgreement.php`
- `app/Services/Work/AgreementSchedulerService.php`

## Odoo source hotspots
- `modules/fieldservice_agreement/models/__init__.py`
- `modules/fieldservice_agreement/models/agreement.py`
- `modules/fieldservice_agreement/models/fsm_equipment.py`
- `modules/fieldservice_agreement/models/fsm_order.py`
- `modules/fieldservice_agreement/models/fsm_person.py`
- `modules/fieldservice_agreement/views/agreement_view.xml`
- `modules/fieldservice_agreement/views/fsm_equipment_view.xml`
- `modules/fieldservice_agreement/views/fsm_order_view.xml`
- `modules/fieldservice_agreement/views/fsm_person.xml`
- `modules/fieldservice_agreement/migrations/18.0.1.1.0/post-migrate.py`
- `modules/fieldservice_agreement/tests/__init__.py`
- `modules/fieldservice_agreement/tests/test_fsm_agreement.py`
- `modules/fieldservice_agreement/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Manage Field Service agreements and contracts

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes