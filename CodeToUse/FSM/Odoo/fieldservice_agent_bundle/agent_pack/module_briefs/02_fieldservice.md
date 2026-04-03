# Module Brief — fieldservice

## Summary

Manage Field Service Locations, Workers and Orders

## Pass order

2

## Internal addon dependencies

- `base_territory`

## Likely zero-main targets

- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/Site.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `routes/core/work.routes.php`
- `resources/views/default/panel/user/work/jobs/*`

## Odoo source hotspots to inspect

- `modules/fieldservice/models/__init__.py`
- `modules/fieldservice/models/fsm_category.py`
- `modules/fieldservice/models/fsm_equipment.py`
- `modules/fieldservice/models/fsm_location.py`
- `modules/fieldservice/models/fsm_location_person.py`
- `modules/fieldservice/models/fsm_model_mixin.py`
- `modules/fieldservice/models/fsm_order.py`
- `modules/fieldservice/models/fsm_order_type.py`
- `modules/fieldservice/models/fsm_person.py`
- `modules/fieldservice/models/fsm_person_calendar_filter.py`
- `modules/fieldservice/models/fsm_stage.py`
- `modules/fieldservice/models/fsm_tag.py`
- `modules/fieldservice/models/fsm_team.py`
- `modules/fieldservice/models/fsm_template.py`
- `modules/fieldservice/models/res_company.py`
- `modules/fieldservice/models/res_config_settings.py`
- `modules/fieldservice/models/res_partner.py`
- `modules/fieldservice/models/res_territory.py`

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