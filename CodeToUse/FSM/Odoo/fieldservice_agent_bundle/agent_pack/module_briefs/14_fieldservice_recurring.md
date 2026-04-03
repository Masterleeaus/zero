# Module Brief — fieldservice_recurring

## Summary

Manage recurring Field Service orders

## Pass order

14

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/ServiceAgreement.php`
- `app/Services/Work/AgreementSchedulerService.php`
- `app/Http/Controllers/Core/Work/ServiceAgreementController.php`
- `resources/views/default/panel/user/work/agreements/*`

## Odoo source hotspots to inspect

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

## Merge strategy

- Port fields, statuses, workflows, and guardrails into existing Titan Zero domains.
- Do not port Odoo framework internals or generic security/runtime glue.
- Keep backend-first; only touch blades/APIs if needed to expose the new capability.
- Reuse company-scoped route/controller patterns already present in zero-main.

## Acceptance checklist

- host models extended or mapped cleanly
- no duplicate domain created
- routes/controllers wired only where needed
- migrations are additive and tenant-safe
- issue/PR notes record the exact zero-main files changed