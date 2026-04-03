# Copilot Issue Prompt — fieldservice_portal

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
- `fieldservice_portal`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/Site.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `customer/portal surfaces`

## Odoo source hotspots
- `modules/fieldservice_portal/models/__init__.py`
- `modules/fieldservice_portal/models/fsm_stage.py`
- `modules/fieldservice_portal/security/ir.model.access.csv`
- `modules/fieldservice_portal/security/portal_security.xml`
- `modules/fieldservice_portal/views/fsm_order_template.xml`
- `modules/fieldservice_portal/views/fsm_stage.xml`
- `modules/fieldservice_portal/views/portal_template.xml`
- `modules/fieldservice_portal/tests/__init__.py`
- `modules/fieldservice_portal/tests/test_portal.py`
- `modules/fieldservice_portal/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Bridge module between fieldservice and portal.

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes