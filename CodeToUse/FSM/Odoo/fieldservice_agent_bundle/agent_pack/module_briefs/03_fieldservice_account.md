# Module Brief — fieldservice_account

## Summary

Track invoices linked to Field Service orders

## Pass order

3

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Money/InvoiceController.php`
- `app/Http/Controllers/Core/Money/PaymentController.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_account/models/__init__.py`
- `modules/fieldservice_account/models/account_move.py`
- `modules/fieldservice_account/models/account_move_line.py`
- `modules/fieldservice_account/models/fsm_order.py`
- `modules/fieldservice_account/models/fsm_stage.py`
- `modules/fieldservice_account/security/ir.model.access.csv`
- `modules/fieldservice_account/views/account_move.xml`
- `modules/fieldservice_account/views/fsm_order.xml`
- `modules/fieldservice_account/views/fsm_stage.xml`
- `modules/fieldservice_account/tests/__init__.py`
- `modules/fieldservice_account/tests/test_fsm_account.py`
- `modules/fieldservice_account/README.rst`

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