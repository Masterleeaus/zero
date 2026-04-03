# FSM Modules: fieldservice_portal + fieldservice_project — Merge Report

**Date:** 2026-04-03  
**Modules:** module_21 (fieldservice_portal) + module_22 (fieldservice_project)  
**Status:** Merged

---

## What Was Merged

### fieldservice_portal
- **JobStage**: Added `portal_visible` boolean (default `true`); `scopePortalVisible()` scope.
- **ServiceJob**: Added `scopePortalVisible()`, `toPortalCard()`, `portalStatusLabel()`, `portalScheduleLabel()`.
- **ServicePlanVisit**: Added `toPortalCard()`, `portalStatusLabel()`, `portalScheduleLabel()`.
- **Customer**: Added portal helper methods: `upcomingPortalVisits()`, `portalServiceHistory()`, `portalInvoices()`, `portalQuotes()`, `portalAssets()`, `portalAgreements()`, `portalPayments()`.
- **PortalController**: Customer-scoped portal views (dashboard, jobs, job detail, invoices, quotes, agreements, assets).
- **Routes**: `routes/core/portal.routes.php` — prefix `portal/service`, name `portal.service.*`.
- **Views**: `resources/views/default/panel/work/portal/` (7 blade views).
- **Events**: `PortalBookingRequested`, `PortalVisitConfirmed`, `PortalQuoteApproved`, `PortalPaymentSubmitted`, `PortalFeedbackSubmitted`, `PortalMessageCreated`.

### fieldservice_project
- **FieldServiceProject**: New model (`app/Models/Work/FieldServiceProject.php`), table `field_service_projects`.
- **ServiceJob**: Added `project_id`, `project_task_ref` to fillable; `project()` BelongsTo relation.
- **ServicePlanVisit**: Added `project_id` to fillable; `project()` BelongsTo relation.
- **Premises**: Added `projects()` HasMany; `upcomingPortalEvents()`, `portalServiceSummary()` helpers.
- **FieldServiceProjectService**: Create/update/linkJob/linkVisit/createJobForProject/checkAndCompleteProject.
- **FieldServiceProjectController**: CRUD + job linking.
- **Routes**: `routes/core/project.routes.php` — prefix `dashboard/work/projects`, name `work.projects.*`.
- **Views**: `resources/views/default/panel/work/projects/` (index, show).
- **Events**: `FieldServiceProjectCreated`, `FieldServiceProjectUpdated`, `FieldServiceProjectJobLinked`, `FieldServiceProjectVisitLinked`, `FieldServiceProjectCompleted`.
- **Listeners**: `FieldServiceProjectCreatedListener`, `FieldServiceProjectCompletedListener`.

---

## Migration

File: `database/migrations/2026_04_03_500200_add_fieldservice_portal_project_columns.php`

Changes:
- `job_stages.portal_visible` boolean (default true)
- `field_service_projects` table created
- `service_jobs.project_id` + `service_jobs.project_task_ref` added
- `service_plan_visits.project_id` added

---

## Overlap Map

See `fieldservice_portal_project_overlap_map.json` in repo root.

Key resolutions:
- **JobStage** — extended (added `portal_visible`)
- **ServiceJob** — extended (added project linkage + portal scopes)
- **ServicePlanVisit** — extended (added project linkage + portal helpers)
- **Customer** — extended (added portal helpers)
- **Premises** — extended (added project relation + portal helpers)
- **FieldServiceProject** — new model (no duplicate; groups jobs/visits under a project)

---

## Tenancy

- `FieldServiceProject` uses `BelongsToCompany` trait (auto-fills `company_id`).
- `PortalController::resolvePortalCustomer()` scopes by `company_id` + `email` match.
- All portal queries are customer-scoped and company-bounded.

---

## Deferred / Out of Scope

- Portal authentication middleware (e.g., `portal.auth`) — deferred to a dedicated auth pass.
- Portal booking form (POST endpoint for requesting new visits) — deferred.
- Project ↔ invoice aggregation — deferred to fieldservice_account merge.
- Analytics on project completion rates — deferred to module_30 (fieldservice_analytics).

---

## Next Module

`module_9` — `fieldservice_calendar`
