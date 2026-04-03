# META PROMPT — Integrate `fieldservice_vehicle` Into Titan Zero Backend

## Objective
Add vehicle assignment support for teams, routes, and jobs.

## Mapping target
**Primary Titan domain:** vehicles

## Copilot instructions
You are integrating the Odoo addon **fieldservice_vehicle** into the existing **zero-main** Titan Zero Laravel repo as a **backend overlay**, not a new subsystem.

### Mandatory rules
- Full repo scan first.
- Do not scaffold a new app, module, or duplicate domain.
- Extend existing Titan Zero backend only.
- Preserve existing features, routes, UI surfaces, and tenancy.
- `company_id` is the tenant boundary.
- Reuse existing conventions:
  - `routes/core/*.routes.php`
  - `app/Http/Controllers/Core/*`
  - `app/Models/*`
  - `app/Services/*`
  - `resources/views/default/panel/user/*`
- Port concepts, workflows, fields, statuses, relations, and useful business rules only.
- Never import Odoo framework code directly.
- If an Odoo concept overlaps an existing Titan Zero feature, merge it into the existing feature instead of creating a parallel one.
- Backend-first integration; only make small supporting API/view changes if necessary.

## Implementation goals
Add vehicle model/table if absent, route/shift/job links, rego, capacity, notes, simple availability checks.

## Scan focus
1. Find overlapping models, migrations, services, controllers, jobs, routes, and views.
2. Map Odoo concepts into existing Titan Zero work/crm/money/support structures.
3. Implement migrations only where truly needed.
4. Add query scopes, services, hooks, resources, and controller actions as needed.
5. Keep naming aligned to service-business / cleaning terminology.

## Avoid
Do not build fleet telematics.

## Deliverable
Return actual code edits only, with:
- changed files list
- new backend capability added
- any migrations introduced
- any signal/pulse hooks added
