# Module Brief — fieldservice_equipment_stock

## Summary

Integrate stock operations with your field service equipments

## Pass order

9

## Internal addon dependencies

- `fieldservice_stock`

## Likely zero-main targets

- `app/Models/Work/Site.php`
- `app/Models/Work/ServiceJob.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_equipment_stock/models/__init__.py`
- `modules/fieldservice_equipment_stock/models/fsm_equipment.py`
- `modules/fieldservice_equipment_stock/models/product_template.py`
- `modules/fieldservice_equipment_stock/models/stock_lot.py`
- `modules/fieldservice_equipment_stock/models/stock_move.py`
- `modules/fieldservice_equipment_stock/models/stock_picking_type.py`
- `modules/fieldservice_equipment_stock/security/ir.model.access.csv`
- `modules/fieldservice_equipment_stock/views/fsm_equipment.xml`
- `modules/fieldservice_equipment_stock/views/product_template.xml`
- `modules/fieldservice_equipment_stock/views/stock_lot.xml`
- `modules/fieldservice_equipment_stock/views/stock_picking_type.xml`
- `modules/fieldservice_equipment_stock/tests/__init__.py`
- `modules/fieldservice_equipment_stock/tests/test_fsm_equipment.py`
- `modules/fieldservice_equipment_stock/tests/test_stock_move.py`
- `modules/fieldservice_equipment_stock/README.rst`

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