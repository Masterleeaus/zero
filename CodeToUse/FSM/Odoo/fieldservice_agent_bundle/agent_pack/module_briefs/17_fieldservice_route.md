# Module Brief — fieldservice_route

## Summary

Organize the routes of each day.

## Pass order

17

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/Shift.php`
- `app/Models/Work/ServiceJob.php`
- `app/Models/Team/Team.php`
- `app/Http/Controllers/Core/Work/ShiftController.php`
- `routes/core/work.routes.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_route/models/__init__.py`
- `modules/fieldservice_route/models/fsm_location.py`
- `modules/fieldservice_route/models/fsm_order.py`
- `modules/fieldservice_route/models/fsm_route.py`
- `modules/fieldservice_route/models/fsm_route_day.py`
- `modules/fieldservice_route/models/fsm_route_dayroute.py`
- `modules/fieldservice_route/models/fsm_stage.py`
- `modules/fieldservice_route/data/fsm_route_day_data.xml`
- `modules/fieldservice_route/data/fsm_stage_data.xml`
- `modules/fieldservice_route/data/ir_sequence.xml`
- `modules/fieldservice_route/security/ir.model.access.csv`
- `modules/fieldservice_route/views/fsm_location.xml`
- `modules/fieldservice_route/views/fsm_order.xml`
- `modules/fieldservice_route/views/fsm_route.xml`
- `modules/fieldservice_route/views/fsm_route_day.xml`
- `modules/fieldservice_route/views/fsm_route_dayroute.xml`
- `modules/fieldservice_route/views/menu.xml`
- `modules/fieldservice_route/tests/__init__.py`

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