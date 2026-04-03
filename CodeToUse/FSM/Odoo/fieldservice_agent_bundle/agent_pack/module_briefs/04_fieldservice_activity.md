# Module Brief — fieldservice_activity

## Summary

Field Service Activities are a set of actions
     that need to be performed on a service order

## Pass order

4

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `resources/views/default/panel/user/work/jobs/show.blade.php`

## Odoo source hotspots to inspect

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