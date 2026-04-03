# Module Brief — fieldservice_repair

## Summary

Integrate Field Service orders with MRP repair orders

## Pass order

15

## Internal addon dependencies

- `fieldservice_equipment_stock`

## Likely zero-main targets

- `app/Models/Support or Work`
- `app/Http/Controllers/Core/Work`

## Odoo source hotspots to inspect

- `modules/fieldservice_repair/models/__init__.py`
- `modules/fieldservice_repair/models/fsm_order.py`
- `modules/fieldservice_repair/models/fsm_order_type.py`
- `modules/fieldservice_repair/models/repair_order.py`
- `modules/fieldservice_repair/data/fsm_order_type.xml`
- `modules/fieldservice_repair/views/fsm_order_view.xml`
- `modules/fieldservice_repair/migrations/18.0.2.0.0/post-migrate.py`
- `modules/fieldservice_repair/tests/__init__.py`
- `modules/fieldservice_repair/tests/test_fsm_repair.py`
- `modules/fieldservice_repair/README.rst`

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