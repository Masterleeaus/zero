# Module Brief — fieldservice_sale_recurring

## Summary

Sell recurring field services.

## Pass order

22

## Internal addon dependencies

- `fieldservice_recurring`
- `fieldservice_sale`
- `fieldservice_account`

## Likely zero-main targets

- `app/Models/Work/ServiceAgreement.php`
- `app/Services/Work/AgreementSchedulerService.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_sale_recurring/models/__init__.py`
- `modules/fieldservice_sale_recurring/models/fsm_recurring.py`
- `modules/fieldservice_sale_recurring/models/product_template.py`
- `modules/fieldservice_sale_recurring/models/sale_order.py`
- `modules/fieldservice_sale_recurring/models/sale_order_line.py`
- `modules/fieldservice_sale_recurring/security/ir.model.access.csv`
- `modules/fieldservice_sale_recurring/views/fsm_recurring.xml`
- `modules/fieldservice_sale_recurring/views/product_template.xml`
- `modules/fieldservice_sale_recurring/views/sale_order.xml`
- `modules/fieldservice_sale_recurring/tests/__init__.py`
- `modules/fieldservice_sale_recurring/tests/test_fsm_sale_recurring.py`
- `modules/fieldservice_sale_recurring/README.rst`

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