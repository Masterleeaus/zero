# Copilot Issue Prompt — fieldservice_stage_server_action

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
- `fieldservice_stage_server_action`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Http/Controllers/TitanSignalApiController.php`
- `routes/core/signals.routes.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`

## Odoo source hotspots
- `modules/fieldservice_stage_server_action/models/__init__.py`
- `modules/fieldservice_stage_server_action/models/fsm_order.py`
- `modules/fieldservice_stage_server_action/models/fsm_stage.py`
- `modules/fieldservice_stage_server_action/data/base_automation.xml`
- `modules/fieldservice_stage_server_action/data/fsm_stage.xml`
- `modules/fieldservice_stage_server_action/data/ir_server_action.xml`
- `modules/fieldservice_stage_server_action/views/fsm_stage.xml`
- `modules/fieldservice_stage_server_action/tests/__init__.py`
- `modules/fieldservice_stage_server_action/tests/test_fsm_order_run_action.py`
- `modules/fieldservice_stage_server_action/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Execute server actions when reaching a Field Service stage

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes