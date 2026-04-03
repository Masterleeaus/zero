# Module Brief — fieldservice_availability

## Summary

Provides models for defining blackout days, stress days, and delivery time ranges for FSM availability management.

## Pass order

6

## Internal addon dependencies

- `fieldservice_route`

## Likely zero-main targets

- `app/Models/Work/Shift.php`
- `app/Models/Work/Site.php`
- `app/Models/Team/TeamMember.php`
- `app/Http/Controllers/Core/Work/ShiftController.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_availability/models/__init__.py`
- `modules/fieldservice_availability/models/fsm_blackout_day.py`
- `modules/fieldservice_availability/models/fsm_blackout_group.py`
- `modules/fieldservice_availability/models/fsm_delivery_time_range.py`
- `modules/fieldservice_availability/models/fsm_stress_day.py`
- `modules/fieldservice_availability/security/ir.model.access.csv`
- `modules/fieldservice_availability/views/fsm_blackout_day_templates.xml`
- `modules/fieldservice_availability/views/fsm_delivery_time_range_templates.xml`
- `modules/fieldservice_availability/views/fsm_stress_day_templates.xml`
- `modules/fieldservice_availability/views/menu.xml`
- `modules/fieldservice_availability/tests/__init__.py`
- `modules/fieldservice_availability/tests/test_fsm_delivery_time_range.py`
- `modules/fieldservice_availability/README.rst`

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