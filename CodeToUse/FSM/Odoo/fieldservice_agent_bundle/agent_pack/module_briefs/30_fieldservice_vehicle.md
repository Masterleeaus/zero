# Module Brief — fieldservice_vehicle

## Summary

Manage Field Service vehicles and assign drivers

## Pass order

30

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Team/Team.php`
- `app/Models/Work/Shift.php`
- `app/Http/Controllers/Core/Work/ShiftController.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_vehicle/models/__init__.py`
- `modules/fieldservice_vehicle/models/fsm_order.py`
- `modules/fieldservice_vehicle/models/fsm_person.py`
- `modules/fieldservice_vehicle/models/fsm_vehicle.py`
- `modules/fieldservice_vehicle/security/ir.model.access.csv`
- `modules/fieldservice_vehicle/security/res_groups.xml`
- `modules/fieldservice_vehicle/views/fsm_order.xml`
- `modules/fieldservice_vehicle/views/fsm_person.xml`
- `modules/fieldservice_vehicle/views/fsm_vehicle.xml`
- `modules/fieldservice_vehicle/views/menu.xml`
- `modules/fieldservice_vehicle/tests/__init__.py`
- `modules/fieldservice_vehicle/tests/test_fsm_vehicle.py`
- `modules/fieldservice_vehicle/README.rst`

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