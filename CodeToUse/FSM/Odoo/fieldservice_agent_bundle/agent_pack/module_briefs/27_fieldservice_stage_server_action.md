# Module Brief — fieldservice_stage_server_action

## Summary

Execute server actions when reaching a Field Service stage

## Pass order

27

## Internal addon dependencies

- `fieldservice`

## Likely zero-main targets

- `app/Http/Controllers/TitanSignalApiController.php`
- `routes/core/signals.routes.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`

## Odoo source hotspots to inspect

- `modules/fieldservice_stage_server_action/models/__init__.py`
- `modules/fieldservice_stage_server_action/models/fsm_order.py`
- `modules/fieldservice_stage_server_action/models/fsm_stage.py`
- `modules/fieldservice_stage_server_action/data/base_automation.xml`
- `modules/fieldservice_stage_server_action/data/fsm_stage.xml`
- `modules/fieldservice_stage_server_action/data/ir_server_action.xml`
- `modules/fieldservice_stage_server_action/views/fsm_stage.xml`
- `modules/fieldservice_stage_server_action/tests/__init__.py`
- `modules/fieldservice_stage_server_action/tests/test_fsm_order_run_action.py`
- `modules/fieldservice_stage_server_action/README.rst`

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