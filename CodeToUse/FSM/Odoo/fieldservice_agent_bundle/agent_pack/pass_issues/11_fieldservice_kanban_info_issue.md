# Copilot Issue Prompt — fieldservice_kanban_info

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
- `fieldservice_kanban_info`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `resources/views/default/panel/user/work/jobs/index.blade.php`
- `resources/views/default/panel/user/crm/deals/kanban.blade.php`

## Odoo source hotspots
- `modules/fieldservice_kanban_info/models/__init__.py`
- `modules/fieldservice_kanban_info/models/fsm_order.py`
- `modules/fieldservice_kanban_info/models/res_config_settings.py`
- `modules/fieldservice_kanban_info/views/fsm_order.xml`
- `modules/fieldservice_kanban_info/views/res_config_settings_views.xml`
- `modules/fieldservice_kanban_info/tests/__init__.py`
- `modules/fieldservice_kanban_info/tests/test_fieldservice_kanban_info.py`
- `modules/fieldservice_kanban_info/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Display key service information on Field Service Kanban cards.

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes