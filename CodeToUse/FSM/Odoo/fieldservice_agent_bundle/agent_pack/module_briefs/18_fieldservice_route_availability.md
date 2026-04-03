# Module Brief — fieldservice_route_availability

## Summary

Restricts blackout days for Scheduled Start (ETA) orders with the same date.

## Pass order

18

## Internal addon dependencies

- `fieldservice_availability`

## Likely zero-main targets

- `app/Services/Work`
- `app/Http/Controllers/Core/Work/ShiftController.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_route_availability/models/__init__.py`
- `modules/fieldservice_route_availability/models/fsm_blackout_day.py`
- `modules/fieldservice_route_availability/models/fsm_order.py`
- `modules/fieldservice_route_availability/models/fsm_route.py`
- `modules/fieldservice_route_availability/views/fsm_blackout_day_templates.xml`
- `modules/fieldservice_route_availability/views/fsm_route.xml`
- `modules/fieldservice_route_availability/tests/__init__.py`
- `modules/fieldservice_route_availability/tests/test_route_availability.py`
- `modules/fieldservice_route_availability/README.rst`

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