# Module Brief — fieldservice_sale_recurring_agreement

## Summary

Field Service Recurring Agreement

## Pass order

23

## Internal addon dependencies

- `fieldservice_agreement`
- `fieldservice_sale_recurring`

## Likely zero-main targets

- `agreements recurrence overlay`

## Odoo source hotspots to inspect

- `modules/fieldservice_sale_recurring_agreement/models/__init__.py`
- `modules/fieldservice_sale_recurring_agreement/models/fsm_recurring.py`
- `modules/fieldservice_sale_recurring_agreement/models/sale_order_line.py`
- `modules/fieldservice_sale_recurring_agreement/views/fsm_recurring.xml`
- `modules/fieldservice_sale_recurring_agreement/README.rst`

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