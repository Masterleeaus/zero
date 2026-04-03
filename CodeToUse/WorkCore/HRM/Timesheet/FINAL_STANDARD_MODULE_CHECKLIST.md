# FINAL — Standard Module Checklist (Validation)

## 0) Declare Module Type
- Type: Tenant
- Scope: Timesheets + timer + approvals + reports; integrates with core Tasks/HR/Work Orders via adapters; does not duplicate core finance/HR engines.

## A) Universal Checks

### A1) Naming + Identity
- Folder/slug: `Timesheet` (module.json consistent)
- Provider/namespace aligns to module.json

### A2) Install + Enable + Packaging
- Migrations provided (idempotent)
- Seeders provided (permissions)
- No Blade entitlements gating

### A3) ServiceProvider Safety
- register() does not early-return
- boot() guarded where appropriate
- Menu injection via event listener (safe)

### A4) Routes + Names
- Tenant routes under `account/timesheets/*`
- All routes use auth middleware + permission gates
- No public helper endpoints

### A5) Sidebar Contract
- Menu listener uses language key
- Safe rendering (no heavy DB calls in sidebar)

### A6) Mandatory Pages
- Timesheets index/create/edit
- Reports pages
- Settings page (permission gated)

### A7) Permissions + Authorization
- Permission keys seeded and enforced:
  - manage/create/edit/delete/export/report/approve/settings/timer
- Policies present for record-level control where used

### A8) Database + Tenancy Safety
- Records scoped by creator/workspace/company patterns
- Indexes added on common report dimensions
- Export/report endpoints scoped and permission gated

### A9) Workflows + Events
- Workflow manifest present (events for CRUD/timer/submission/review)

### A10) Voice / Intent Readiness
- No voice execution; ready for Titan Zero intent wiring later (structured endpoints exist)

### A11) Language + Verticals
- Menu/UI strings moved to language keys (tradie labels ready)

### A12) Cron + Queues
- None required in this module (no scheduled jobs)

### A13) API/Integrations
- API reporting endpoints are auth-protected
- Integrations use adapter pattern and are guarded

### A14) Assets
- No global asset collisions introduced

### A15) Smoke Tests (recommended)
- Fresh install: migrate + seed + clear cache
- Enable/disable: menu renders
- Permissions off: menu hidden + route forbidden
- Timer start/stop: creates entries
- Submit/approve flow works
- Reports load without heavy queries (indexes present)
