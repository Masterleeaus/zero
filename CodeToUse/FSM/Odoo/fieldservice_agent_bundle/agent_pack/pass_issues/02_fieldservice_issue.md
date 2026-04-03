# Copilot Issue Prompt — fieldservice

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
- `fieldservice`

## Internal addon dependencies
- `base_territory`

## zero-main targets to inspect
- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/Site.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `routes/core/work.routes.php`
- `resources/views/default/panel/user/work/jobs/*`

## Odoo source hotspots
- `modules/fieldservice/models/__init__.py`
- `modules/fieldservice/models/fsm_category.py`
- `modules/fieldservice/models/fsm_equipment.py`
- `modules/fieldservice/models/fsm_location.py`
- `modules/fieldservice/models/fsm_location_person.py`
- `modules/fieldservice/models/fsm_model_mixin.py`
- `modules/fieldservice/models/fsm_order.py`
- `modules/fieldservice/models/fsm_order_type.py`
- `modules/fieldservice/models/fsm_person.py`
- `modules/fieldservice/models/fsm_person_calendar_filter.py`
- `modules/fieldservice/models/fsm_stage.py`
- `modules/fieldservice/models/fsm_tag.py`
- `modules/fieldservice/models/fsm_team.py`
- `modules/fieldservice/models/fsm_template.py`
- `modules/fieldservice/models/res_company.py`
- `modules/fieldservice/models/res_config_settings.py`
- `modules/fieldservice/models/res_partner.py`
- `modules/fieldservice/models/res_territory.py`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Manage Field Service Locations, Workers and Orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes