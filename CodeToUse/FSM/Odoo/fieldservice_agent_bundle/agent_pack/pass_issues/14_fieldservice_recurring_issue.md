# Copilot Issue Prompt — fieldservice_recurring

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
- `fieldservice_recurring`

## Internal addon dependencies
- `fieldservice`

## zero-main targets to inspect
- `app/Models/Work/ServiceAgreement.php`
- `app/Services/Work/AgreementSchedulerService.php`
- `app/Http/Controllers/Core/Work/ServiceAgreementController.php`
- `resources/views/default/panel/user/work/agreements/*`

## Odoo source hotspots
- `modules/fieldservice_recurring/models/__init__.py`
- `modules/fieldservice_recurring/models/fsm_frequency.py`
- `modules/fieldservice_recurring/models/fsm_frequency_set.py`
- `modules/fieldservice_recurring/models/fsm_order.py`
- `modules/fieldservice_recurring/models/fsm_recurring.py`
- `modules/fieldservice_recurring/models/fsm_recurring_template.py`
- `modules/fieldservice_recurring/models/fsm_team.py`
- `modules/fieldservice_recurring/data/ir_sequence.xml`
- `modules/fieldservice_recurring/data/recurring_cron.xml`
- `modules/fieldservice_recurring/security/ir.model.access.csv`
- `modules/fieldservice_recurring/security/recurring_security.xml`
- `modules/fieldservice_recurring/security/res_groups.xml`
- `modules/fieldservice_recurring/views/fsm_frequency.xml`
- `modules/fieldservice_recurring/views/fsm_frequency_set.xml`
- `modules/fieldservice_recurring/views/fsm_order.xml`
- `modules/fieldservice_recurring/views/fsm_recurring.xml`
- `modules/fieldservice_recurring/views/fsm_recurring_template.xml`
- `modules/fieldservice_recurring/views/fsm_team.xml`

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- Manage recurring Field Service orders

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes