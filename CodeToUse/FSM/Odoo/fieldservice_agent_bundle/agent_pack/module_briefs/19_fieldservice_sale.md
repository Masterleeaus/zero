# Module Brief — fieldservice_sale

## Summary

Sell field services.

## Pass order

19

## Internal addon dependencies

- `fieldservice`
- `fieldservice_account`

## Likely zero-main targets

- `app/Http/Controllers/Core/Money/QuoteController.php`
- `app/Services/Money/QuoteService.php`
- `app/Models/Work/ServiceJob.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_sale/models/__init__.py`
- `modules/fieldservice_sale/models/fsm_order.py`
- `modules/fieldservice_sale/models/product_template.py`
- `modules/fieldservice_sale/models/sale_order.py`
- `modules/fieldservice_sale/models/sale_order_line.py`
- `modules/fieldservice_sale/security/ir.model.access.csv`
- `modules/fieldservice_sale/security/res_groups.xml`
- `modules/fieldservice_sale/views/fsm_location.xml`
- `modules/fieldservice_sale/views/fsm_order.xml`
- `modules/fieldservice_sale/views/product_template.xml`
- `modules/fieldservice_sale/views/sale_order.xml`
- `modules/fieldservice_sale/tests/__init__.py`
- `modules/fieldservice_sale/tests/test_fsm_sale_autofill_location.py`
- `modules/fieldservice_sale/tests/test_fsm_sale_common.py`
- `modules/fieldservice_sale/tests/test_fsm_sale_order.py`
- `modules/fieldservice_sale/README.rst`

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