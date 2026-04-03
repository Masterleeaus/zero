# Module Brief — fieldservice_portal

## Summary

Bridge module between fieldservice and portal.

## Pass order

12

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/Site.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `customer/portal surfaces`

## Odoo source hotspots to inspect

- `modules/fieldservice_portal/models/__init__.py`
- `modules/fieldservice_portal/models/fsm_stage.py`
- `modules/fieldservice_portal/security/ir.model.access.csv`
- `modules/fieldservice_portal/security/portal_security.xml`
- `modules/fieldservice_portal/views/fsm_order_template.xml`
- `modules/fieldservice_portal/views/fsm_stage.xml`
- `modules/fieldservice_portal/views/portal_template.xml`
- `modules/fieldservice_portal/tests/__init__.py`
- `modules/fieldservice_portal/tests/test_portal.py`
- `modules/fieldservice_portal/README.rst`

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