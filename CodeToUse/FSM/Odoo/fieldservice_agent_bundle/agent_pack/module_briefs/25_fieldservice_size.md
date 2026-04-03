# Module Brief — fieldservice_size

## Summary

Manage Sizes for Field Service Locations and Orders

## Pass order

25

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/Site.php`
- `app/Models/Work/ServiceJob.php`
- `app/Http/Controllers/Core/Money/QuoteController.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_size/models/__init__.py`
- `modules/fieldservice_size/models/fsm_location.py`
- `modules/fieldservice_size/models/fsm_location_size.py`
- `modules/fieldservice_size/models/fsm_order.py`
- `modules/fieldservice_size/models/fsm_size.py`
- `modules/fieldservice_size/security/ir.model.access.csv`
- `modules/fieldservice_size/views/fsm_location.xml`
- `modules/fieldservice_size/views/fsm_order.xml`
- `modules/fieldservice_size/views/fsm_size.xml`
- `modules/fieldservice_size/views/menu.xml`
- `modules/fieldservice_size/tests/__init__.py`
- `modules/fieldservice_size/tests/test_fsm_order.py`
- `modules/fieldservice_size/README.rst`

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