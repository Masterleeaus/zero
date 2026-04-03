# Module Brief — base_territory

## Summary

This module allows you to define territories, branches, districts and regions to be used for Field Service operations or Sales.

## Pass order

1

## Internal addon dependencies

- none

## Likely zero-main targets

- `app/Models/Work/Site.php`
- `app/Http/Controllers/Core/Team/ZoneController.php`
- `routes/core/team.routes.php`
- `resources/views/default/panel/user/team/zones/*`

## Odoo source hotspots to inspect

- `modules/base_territory/models/__init__.py`
- `modules/base_territory/models/res_branch.py`
- `modules/base_territory/models/res_country.py`
- `modules/base_territory/models/res_district.py`
- `modules/base_territory/models/res_region.py`
- `modules/base_territory/models/res_territory.py`
- `modules/base_territory/security/ir.model.access.csv`
- `modules/base_territory/views/menu.xml`
- `modules/base_territory/views/res_branch.xml`
- `modules/base_territory/views/res_country.xml`
- `modules/base_territory/views/res_district.xml`
- `modules/base_territory/views/res_region.xml`
- `modules/base_territory/views/res_territory.xml`
- `modules/base_territory/tests/__init__.py`
- `modules/base_territory/tests/test_res_branch.py`
- `modules/base_territory/tests/test_res_country.py`
- `modules/base_territory/tests/test_res_district.py`
- `modules/base_territory/tests/test_res_region.py`

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