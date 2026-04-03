# Module Brief — fieldservice_agreement

## Summary

Manage Field Service agreements and contracts

## Pass order

5

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Work/ServiceAgreement.php`
- `app/Services/Work/AgreementSchedulerService.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_agreement/models/__init__.py`
- `modules/fieldservice_agreement/models/agreement.py`
- `modules/fieldservice_agreement/models/fsm_equipment.py`
- `modules/fieldservice_agreement/models/fsm_order.py`
- `modules/fieldservice_agreement/models/fsm_person.py`
- `modules/fieldservice_agreement/views/agreement_view.xml`
- `modules/fieldservice_agreement/views/fsm_equipment_view.xml`
- `modules/fieldservice_agreement/views/fsm_order_view.xml`
- `modules/fieldservice_agreement/views/fsm_person.xml`
- `modules/fieldservice_agreement/migrations/18.0.1.1.0/post-migrate.py`
- `modules/fieldservice_agreement/tests/__init__.py`
- `modules/fieldservice_agreement/tests/test_fsm_agreement.py`
- `modules/fieldservice_agreement/README.rst`

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