# Module Brief — fieldservice_crm

## Summary

Create Field Service orders from the CRM

## Pass order

8

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Crm/Enquiry.php`
- `app/Models/Crm/Customer.php`
- `app/Http/Controllers/Core/Crm/EnquiryController.php`
- `app/Http/Controllers/Core/Money/QuoteController.php`
- `routes/core/crm.routes.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_crm/models/__init__.py`
- `modules/fieldservice_crm/models/crm_lead.py`
- `modules/fieldservice_crm/models/fsm_location.py`
- `modules/fieldservice_crm/models/fsm_order.py`
- `modules/fieldservice_crm/security/ir.model.access.csv`
- `modules/fieldservice_crm/views/crm_lead.xml`
- `modules/fieldservice_crm/views/fsm_location.xml`
- `modules/fieldservice_crm/views/fsm_order.xml`
- `modules/fieldservice_crm/tests/__init__.py`
- `modules/fieldservice_crm/tests/test_fsm_crm.py`
- `modules/fieldservice_crm/README.rst`

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