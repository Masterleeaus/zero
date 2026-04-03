# Module Brief — fieldservice_equipment_warranty

## Summary

Field Service equipment warranty

## Pass order

10

## Internal addon dependencies

- `fieldservice_equipment_stock`

## Likely zero-main targets

- `equipment/assets domain or new ext_* tables`

## Odoo source hotspots to inspect

- `modules/fieldservice_equipment_warranty/models/__init__.py`
- `modules/fieldservice_equipment_warranty/models/fsm_equipment.py`
- `modules/fieldservice_equipment_warranty/views/fsm_equipment.xml`
- `modules/fieldservice_equipment_warranty/tests/__init__.py`
- `modules/fieldservice_equipment_warranty/tests/test_fsm_equipment_warranty.py`
- `modules/fieldservice_equipment_warranty/README.rst`

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