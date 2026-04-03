# Module Brief — fieldservice_stock

## Summary

Integrate the logistics operations with Field Service

## Pass order

28

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_stock/models/__init__.py`
- `modules/fieldservice_stock/models/fsm_location.py`
- `modules/fieldservice_stock/models/fsm_order.py`
- `modules/fieldservice_stock/models/fsm_wizard.py`
- `modules/fieldservice_stock/models/procurement_group.py`
- `modules/fieldservice_stock/models/res_territory.py`
- `modules/fieldservice_stock/models/stock_move.py`
- `modules/fieldservice_stock/models/stock_picking.py`
- `modules/fieldservice_stock/models/stock_rule.py`
- `modules/fieldservice_stock/data/fsm_stock_data.xml`
- `modules/fieldservice_stock/security/ir.model.access.csv`
- `modules/fieldservice_stock/views/fsm_location.xml`
- `modules/fieldservice_stock/views/fsm_order.xml`
- `modules/fieldservice_stock/views/res_territory.xml`
- `modules/fieldservice_stock/views/stock.xml`
- `modules/fieldservice_stock/views/stock_picking.xml`
- `modules/fieldservice_stock/tests/__init__.py`
- `modules/fieldservice_stock/tests/test_fsm_stock.py`

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