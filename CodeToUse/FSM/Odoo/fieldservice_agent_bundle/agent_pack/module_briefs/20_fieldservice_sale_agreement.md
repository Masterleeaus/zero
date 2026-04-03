# Module Brief — fieldservice_sale_agreement

## Summary

Integrate Field Service with Sale Agreements

## Pass order

20

## Internal addon dependencies

- `fieldservice_agreement`
- `fieldservice_sale`

## Likely zero-main targets

- `app/Http/Controllers/Core/Money/QuoteController.php`
- `app/Models/Work/ServiceAgreement.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_sale_agreement/models/__init__.py`
- `modules/fieldservice_sale_agreement/models/sale_order.py`
- `modules/fieldservice_sale_agreement/tests/__init__.py`
- `modules/fieldservice_sale_agreement/tests/test_fsm_sale_agreement.py`
- `modules/fieldservice_sale_agreement/README.rst`

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