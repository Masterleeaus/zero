# Copilot Issue Prompt — fieldservice_activity

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
- `fieldservice_activity`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `resources/views/default/panel/user/work/jobs/show.blade.php`

## Odoo source hotspots
- `modules/fieldservice_activity/models/__init__.py`
- `modules/fieldservice_activity/models/fsm_activity.py`
- `modules/fieldservice_activity/models/fsm_order.py`
- `modules/fieldservice_activity/models/fsm_template.py`
- `modules/fieldservice_activity/security/ir.model.access.csv`
- `modules/fieldservice_activity/views/fsm_order.xml`
- `modules/fieldservice_activity/views/fsm_template.xml`
- `modules/fieldservice_activity/tests/__init__.py`
- `modules/fieldservice_activity/tests/test_fsm_activity.py`
- `modules/fieldservice_activity/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Field Service Activities are a set of actions      that need to be performed on a service order

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes