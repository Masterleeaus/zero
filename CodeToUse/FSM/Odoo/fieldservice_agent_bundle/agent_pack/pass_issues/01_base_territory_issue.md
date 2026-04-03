# Copilot Issue Prompt — base_territory

You are integrating **one** Odoo Field Service addon into the existing **zero-main** Laravel host app.

## Hard rules
- full scan first: selected Odoo module + exact zero-main target files
- do not scaffold a new application/module
- do not copy Odoo runtime code directly
- extend existing Titan Zero domains first
- use `company_id` tenancy for new work
- keep routes in `routes/core/*.routes.php`
- keep controllers in `app/Http/Controllers/Core/*`
- keep models in `app/Models/*`
- keep views in `resources/views/default/panel/user/*`

## Module
- `base_territory`

## Internal addon dependencies
- none

## zero-main targets to inspect
- `app/Models/Work/Site.php`
- `app/Http/Controllers/Core/Team/ZoneController.php`
- `routes/core/team.routes.php`
- `resources/views/default/panel/user/team/zones/*`

## Odoo source hotspots
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

## What to deliver
- additive migration(s) only if host lacks equivalent fields/tables
- model/controller/service updates in existing host domains
- route wiring only if the host needs new endpoints/screens
- acceptance notes listing exact changed files

## Module goal
- This module allows you to define territories, branches, districts and regions to be used for Field Service operations or Sales.

## Done means
- feature is represented in backend structure
- no duplicate subsystem exists
- host naming stays service-business aligned
- changes are ready for follow-on UI/mobile passes