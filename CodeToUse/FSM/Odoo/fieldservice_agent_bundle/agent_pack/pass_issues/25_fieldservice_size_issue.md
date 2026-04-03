# Copilot Issue Prompt — fieldservice_size

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
- `fieldservice_size`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/Site.php`
- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Money/QuoteController.php`

## Odoo source hotspots
- `modules/fieldservice_size/models/__init__.py`
- `modules/fieldservice_size/models/fsm_location.py`
- `modules/fieldservice_size/models/fsm_location_size.py`
- `modules/fieldservice_size/models/fsm_order.py`
- `modules/fieldservice_size/models/fsm_size.py`
- `modules/fieldservice_size/security/ir.model.access.csv`
- `modules/fieldservice_size/views/fsm_location.xml`
- `modules/fieldservice_size/views/fsm_order.xml`
- `modules/fieldservice_size/views/fsm_size.xml`
- `modules/fieldservice_size/views/menu.xml`
- `modules/fieldservice_size/tests/__init__.py`
- `modules/fieldservice_size/tests/test_fsm_order.py`
- `modules/fieldservice_size/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Manage Sizes for Field Service Locations and Orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes