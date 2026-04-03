# Copilot Issue Prompt — fieldservice_crm

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
- `fieldservice_crm`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Crm/Enquiry.php`
- `app/Models/Crm/Customer.php`
- `app/Http/Controllers/Core/Crm/EnquiryController.php`
- `app/Http/Controllers/Core/Money/QuoteController.php`
- `routes/core/crm.routes.php`

## Odoo source hotspots
- `modules/fieldservice_crm/models/__init__.py`
- `modules/fieldservice_crm/models/crm_lead.py`
- `modules/fieldservice_crm/models/fsm_location.py`
- `modules/fieldservice_crm/models/fsm_order.py`
- `modules/fieldservice_crm/security/ir.model.access.csv`
- `modules/fieldservice_crm/views/crm_lead.xml`
- `modules/fieldservice_crm/views/fsm_location.xml`
- `modules/fieldservice_crm/views/fsm_order.xml`
- `modules/fieldservice_crm/tests/__init__.py`
- `modules/fieldservice_crm/tests/test_fsm_crm.py`
- `modules/fieldservice_crm/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Create Field Service orders from the CRM

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes