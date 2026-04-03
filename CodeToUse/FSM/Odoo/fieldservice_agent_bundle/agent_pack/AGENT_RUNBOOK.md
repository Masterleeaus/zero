# Agent Runbook

## Before each pass
1. Read `agent_pack/repo_scan/zero_main_scan_summary.md`.
2. Read the selected module brief in `agent_pack/module_briefs/`.
3. Read the matching issue prompt in `agent_pack/pass_issues/`.
4. Scan matching Odoo source files under `modules/<module>/`.
5. Scan the exact zero-main target files listed in the module brief.

## Output expectation per pass
- backend-first code changes only
- no new standalone subsystem
- no Odoo runtime imports
- add acceptance notes into PR / issue comment

## Default Laravel targets
- models: `app/Models/Work`, `app/Models/Crm`, `app/Models/Team`
- controllers: `app/Http/Controllers/Core/*`
- services: `app/Services/Work`, `app/Services/Money`
- routes: `routes/core/*.routes.php`
- blades: `resources/views/default/panel/user/*`

## Merge doctrine
- extend existing domains first
- create new tables only if the host app has no equivalent
- prefer company-scoped records
- keep route names in the existing dashboard namespaces
- use Titan signals/Pulse for stage-triggered automation hooks