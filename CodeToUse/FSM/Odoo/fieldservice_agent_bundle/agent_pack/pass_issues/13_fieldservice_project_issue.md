# Copilot Issue Prompt — fieldservice_project

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
- `fieldservice_project`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`

## Odoo source hotspots
- `modules/fieldservice_project/models/__init__.py`
- `modules/fieldservice_project/models/fsm_location.py`
- `modules/fieldservice_project/models/fsm_order.py`
- `modules/fieldservice_project/models/fsm_team.py`
- `modules/fieldservice_project/models/project.py`
- `modules/fieldservice_project/models/project_task.py`
- `modules/fieldservice_project/security/ir.model.access.csv`
- `modules/fieldservice_project/views/fsm_location_views.xml`
- `modules/fieldservice_project/views/fsm_order_views.xml`
- `modules/fieldservice_project/views/fsm_team.xml`
- `modules/fieldservice_project/views/project_task_views.xml`
- `modules/fieldservice_project/views/project_views.xml`
- `modules/fieldservice_project/tests/__init__.py`
- `modules/fieldservice_project/tests/common.py`
- `modules/fieldservice_project/tests/test_fsm_location.py`
- `modules/fieldservice_project/tests/test_fsm_order.py`
- `modules/fieldservice_project/tests/test_project.py`
- `modules/fieldservice_project/tests/test_project_task.py`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Create field service orders from a project or project task

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes