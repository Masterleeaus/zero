# Module Brief — fieldservice_project

## Summary

Create field service orders from a project or project task

## Pass order

13

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`

## Odoo source hotspots to inspect

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