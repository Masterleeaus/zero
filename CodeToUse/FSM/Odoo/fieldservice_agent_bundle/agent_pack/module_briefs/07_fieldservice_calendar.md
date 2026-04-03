# Module Brief — fieldservice_calendar

## Summary

Add calendar to FSM Orders

## Pass order

7

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `app/Services/Work`
- `routes/core/work.routes.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_calendar/models/__init__.py`
- `modules/fieldservice_calendar/models/calendar.py`
- `modules/fieldservice_calendar/models/fsm_order.py`
- `modules/fieldservice_calendar/models/fsm_team.py`
- `modules/fieldservice_calendar/views/fsm_order.xml`
- `modules/fieldservice_calendar/views/fsm_team.xml`
- `modules/fieldservice_calendar/tests/__init__.py`
- `modules/fieldservice_calendar/tests/test_fsm_calendar.py`
- `modules/fieldservice_calendar/README.rst`

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