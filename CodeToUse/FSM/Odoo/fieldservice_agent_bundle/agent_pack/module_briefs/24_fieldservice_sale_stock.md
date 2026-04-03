# Module Brief — fieldservice_sale_stock

## Summary

Sell stockable items linked to field service orders.

## Pass order

24

## Internal addon dependencies

- `fieldservice_sale`
- `fieldservice_stock`

## Likely zero-main targets

- `app/Http/Controllers/Core/Money/QuoteController.php`
- `app/Models/Work/ServiceJob.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_sale_stock/models/__init__.py`
- `modules/fieldservice_sale_stock/models/sale_order.py`
- `modules/fieldservice_sale_stock/tests/__init__.py`
- `modules/fieldservice_sale_stock/tests/test_fsm_sale_order.py`
- `modules/fieldservice_sale_stock/README.rst`

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