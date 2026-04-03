# Odoo Field Service → Titan Zero Backend Overlay Passes

This bundle contains GitHub Copilot-ready meta prompts for integrating the Odoo field service addon set into the zero-main Titan Zero repo as backend overlays.

## Core rules
- Full repo scan first
- Extend existing Titan Zero backend only
- Do not scaffold a new app/module
- Preserve current tenancy and company_id boundary
- Reuse existing route, controller, model, service, and blade conventions
- Port concepts, workflows, fields, statuses, relations, and useful business rules only
- Never import Odoo framework code directly
- Merge into existing domains instead of creating duplicates
- Backend-first integration; keep frontend/mobile changes minimal unless required
- Use Titan naming:
  - customer
  - site
  - service_job
  - cleaner
  - service_issue
  - timelog
  - service_agreement

## Repo conventions to preserve
- routes/core/*.routes.php
- app/Http/Controllers/Core/*
- app/Models/*
- app/Services/*
- resources/views/default/panel/user/*

## Recommended execution order
1. base_territory
2. fieldservice
3. fieldservice_calendar
4. fieldservice_activity
5. fieldservice_kanban_info
6. fieldservice_crm
7. fieldservice_project
8. fieldservice_recurring
9. fieldservice_route
10. fieldservice_availability
11. fieldservice_route_availability
12. fieldservice_skill
13. fieldservice_vehicle
14. fieldservice_timesheet
15. fieldservice_portal
16. fieldservice_account
17. fieldservice_sale
18. fieldservice_sale_stock
19. fieldservice_stock
20. fieldservice_equipment_stock
21. fieldservice_equipment_warranty
22. fieldservice_repair
23. fieldservice_repair_order_template
24. fieldservice_agreement
25. fieldservice_sale_agreement
26. fieldservice_sale_agreement_equipment_stock
27. fieldservice_sale_recurring
28. fieldservice_sale_recurring_agreement
29. fieldservice_size
30. fieldservice_stage_server_action
