# Copilot Issue Prompt — fieldservice_skill

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
- `fieldservice_skill`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Team/TeamMember.php`
- `app/Http/Controllers/Core/Team/CleanerProfileController.php`
- `resources/views/default/panel/user/team/cleaners/*`

## Odoo source hotspots
- `modules/fieldservice_skill/models/__init__.py`
- `modules/fieldservice_skill/models/fsm_category.py`
- `modules/fieldservice_skill/models/fsm_order.py`
- `modules/fieldservice_skill/models/fsm_person.py`
- `modules/fieldservice_skill/models/fsm_person_skill.py`
- `modules/fieldservice_skill/models/fsm_template.py`
- `modules/fieldservice_skill/models/hr_skill.py`
- `modules/fieldservice_skill/security/ir.model.access.csv`
- `modules/fieldservice_skill/views/fsm_category.xml`
- `modules/fieldservice_skill/views/fsm_order.xml`
- `modules/fieldservice_skill/views/fsm_person.xml`
- `modules/fieldservice_skill/views/fsm_person_skill.xml`
- `modules/fieldservice_skill/views/fsm_template.xml`
- `modules/fieldservice_skill/views/hr_skill.xml`
- `modules/fieldservice_skill/tests/__init__.py`
- `modules/fieldservice_skill/tests/test_fsm_skill.py`
- `modules/fieldservice_skill/README.rst`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Manage your Field Service workers skills

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes