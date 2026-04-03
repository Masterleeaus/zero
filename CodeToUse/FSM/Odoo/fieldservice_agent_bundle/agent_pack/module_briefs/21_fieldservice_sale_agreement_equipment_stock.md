# Module Brief — fieldservice_sale_agreement_equipment_stock

## Summary

Integrate Field Service with Sale Agreements and Stock Equipment

## Pass order

21

## Internal addon dependencies

- `fieldservice_agreement`
- `fieldservice_sale`
- `fieldservice_equipment_stock`

## Likely zero-main targets

- `agreements + equipment overlay`

## Odoo source hotspots to inspect

- `modules/fieldservice_sale_agreement_equipment_stock/models/__init__.py`
- `modules/fieldservice_sale_agreement_equipment_stock/models/stock_move.py`
- `modules/fieldservice_sale_agreement_equipment_stock/tests/__init__.py`
- `modules/fieldservice_sale_agreement_equipment_stock/tests/test_fsm_sale_agreement_equipment_stock.py`
- `modules/fieldservice_sale_agreement_equipment_stock/README.rst`

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