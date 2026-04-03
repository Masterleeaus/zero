# Odoo Field Service → Titan Zero Agent-Ready Bundle

This upgraded bundle is designed for GitHub Copilot / coding agents to integrate the Odoo Field Service overlay into **zero-main** with fewer wrong turns.

## What is inside
- original `modules/` source tree for all 30 relevant Odoo addons
- original `prompts/` pass prompts
- new `agent_pack/` folder with repo scan summaries, dependency graph, integration matrices, module briefs, and issue-ready prompts

## Site facts from zero-main scan
- Existing core work models: ServiceJob, Site, Checklist, Timelog, Shift, Attendance, ServiceAgreement, Leave, LeaveQuota, LeaveHistory
- Existing CRM models: Customer, Enquiry
- Existing team models: Team, TeamMember
- Existing work controllers already live under `app/Http/Controllers/Core/Work/`
- Existing route files already live under `routes/core/*.routes.php`
- Existing themed blades already live under `resources/views/default/panel/user/`
- Existing agreement scheduler service already exists: `app/Services/Work/AgreementSchedulerService.php`

## Agent rules
1. Treat `zero-main` as the host app and extend it.
2. Do not port Odoo framework code 1:1.
3. Port workflows, statuses, fields, constraints, and useful business rules.
4. Merge into existing work/crm/team/money domains before adding anything new.
5. Use `company_id` as tenant boundary in new work.
6. Keep route additions in `routes/core/*.routes.php`.
7. Prefer small backend-first passes with acceptance checks.
