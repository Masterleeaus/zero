# HRM Integration Risks

## Risk Register

### HIGH

| Risk | Description | Mitigation |
|------|-------------|------------|
| WeeklyTimesheet controller previously used stub | Replacing WorkcoreDemoData with real DB queries changes index/show response shapes — views may break | Views use flexible blade templates; test assertions cover response shape |
| TimesheetPolicy not registered in AuthServiceProvider | Policy will silently not apply | Must add to AuthServiceProvider model-policy map or use `#[Policy]` attribute |
| BelongsToCompany global scope on TimesheetSubmission | Queries without company context (e.g. migrations, seeds) will return no results | Use `withoutGlobalScope` or `forCompany()` scope in seeds |

### MEDIUM

| Risk | Description | Mitigation |
|------|-------------|------------|
| timelogs relationship on WeeklyTimesheet | Existing `timelogs()` on WeeklyTimesheet is user-scoped, not date-scoped — calculateWeekHours must filter by date | TimesheetService::calculateWeekHours queries Timelog directly with date range |
| StaffProfile user_id unique index | A user can only have one StaffProfile per application (not per company) | Acceptable for this architecture — one staff profile per user is correct |
| manager_id self-referential on staff_profiles | Circular manager chains are possible | Not enforced at DB level; application logic should guard depth if needed |

### LOW

| Risk | Description | Mitigation |
|------|-------------|------------|
| TimesheetTimer deferred | No timer tracking in this pass | Documented as deferred; no breaking change |
| Blade views for staff-profiles are stubs | No real UI for staff profiles | Placeholder view pattern exists in host (CoreController::placeholder()) |
| Factory not created for TimesheetSubmission/StaffProfile | Tests may fail if factories are needed | Tests create records via Eloquent directly |

## Open Items

- AuthServiceProvider policy registration for TimesheetPolicy and StaffProfile policy
- Blade views for staff-profiles need proper templates in a future pass
- Listener implementations for HRM events (notifications, payroll triggers) deferred
