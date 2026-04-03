# Module Brief — fieldservice_skill

## Summary

Manage your Field Service workers skills

## Pass order

26

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Models/Team/TeamMember.php`
- `app/Http/Controllers/Core/Team/CleanerProfileController.php`
- `resources/views/default/panel/user/team/cleaners/*`

## Odoo source hotspots to inspect

- `modules/fieldservice_skill/models/__init__.py`
- `modules/fieldservice_skill/models/fsm_category.py`
- `modules/fieldservice_skill/models/fsm_order.py`
- `modules/fieldservice_skill/models/fsm_person.py`
- `modules/fieldservice_skill/models/fsm_person_skill.py`
- `modules/fieldservice_skill/models/fsm_template.py`
- `modules/fieldservice_skill/models/hr_skill.py`
- `modules/fieldservice_skill/security/ir.model.access.csv`
- `modules/fieldservice_skill/views/fsm_category.xml`
- `modules/fieldservice_skill/views/fsm_order.xml`
- `modules/fieldservice_skill/views/fsm_person.xml`
- `modules/fieldservice_skill/views/fsm_person_skill.xml`
- `modules/fieldservice_skill/views/fsm_template.xml`
- `modules/fieldservice_skill/views/hr_skill.xml`
- `modules/fieldservice_skill/tests/__init__.py`
- `modules/fieldservice_skill/tests/test_fsm_skill.py`
- `modules/fieldservice_skill/README.rst`

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