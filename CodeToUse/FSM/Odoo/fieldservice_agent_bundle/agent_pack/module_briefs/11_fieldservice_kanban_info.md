# Module Brief — fieldservice_kanban_info

## Summary

Display key service information on Field Service Kanban cards.

## Pass order

11

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `resources/views/default/panel/user/work/jobs/index.blade.php`
- `resources/views/default/panel/user/crm/deals/kanban.blade.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_kanban_info/models/__init__.py`
- `modules/fieldservice_kanban_info/models/fsm_order.py`
- `modules/fieldservice_kanban_info/models/res_config_settings.py`
- `modules/fieldservice_kanban_info/views/fsm_order.xml`
- `modules/fieldservice_kanban_info/views/res_config_settings_views.xml`
- `modules/fieldservice_kanban_info/tests/__init__.py`
- `modules/fieldservice_kanban_info/tests/test_fieldservice_kanban_info.py`
- `modules/fieldservice_kanban_info/README.rst`

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