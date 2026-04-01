# Module 5 & 6 Merge Report

## Stage A — Module 5 Completion (fieldservice_kanban_info)

### Overview
Module 5 introduced kanban card metadata for service job boards. The core
`schedule_time_range` accessor was already present in `ServiceJob`. This pass
completed the remaining backend helpers.

### Completed in this pass

| Helper | Location | Notes |
|--------|----------|-------|
| `schedule_time_range` accessor (timezone-aware) | `ServiceJob::getScheduleTimeRangeAttribute()` | Upgraded to use `config('app.timezone')` for proper tz conversion |
| `scheduled_duration_formatted` accessor | `ServiceJob::getScheduledDurationFormattedAttribute()` | Returns "2h 30m" style label; falls back to actual duration |
| `scheduled_window_label` accessor | `ServiceJob::getScheduledWindowLabelAttribute()` | Returns "Today", "Tomorrow", or "dd/mm/yyyy" |
| `boardSummary()` helper | `ServiceJob::boardSummary()` | Compact array for kanban/dispatch card rendering; includes CRM link IDs |
| `toCalendarEvent()` helper | `ServiceJob::toCalendarEvent()` | FullCalendar-compatible array with color from stage |
| `scopeScheduledToday` | `ServiceJob` | Filter jobs with start on today (app tz) |
| `scopeScheduledTomorrow` | `ServiceJob` | Filter jobs with start on tomorrow (app tz) |
| `scopeScheduledThisWeek` | `ServiceJob` | Filter jobs within current Mon–Sun week |
| `scopeSortedForDispatch` | `ServiceJob` | ORDER BY priority (urgent→low) then scheduled_date_start |

### Intentionally skipped

| Item | Reason |
|------|--------|
| Odoo `res.config.settings` panel field | No settings UI in scope; config key `workcore.schedule_time_range_format` already present |
| Kanban JS card widget | Mobile/UI pass; not a backend concern |
| Cross-day colour coding in views | UI-only; the `boardSummary()` exposes `is_overdue` for UI consumers |

---

## Stage B — Module 6 Merge (fieldservice_crm)

### Overview
Module 6 links CRM leads/opportunities to Field Service orders. In Titan Zero
vocabulary: **Enquiry** = lead, **Deal** = opportunity. The host already has
`Enquiry` and `Deal` models; this pass adds the conversion bridge.

### Files changed or created

| File | Action | Purpose |
|------|--------|---------|
| `database/migrations/2026_04_01_000500_add_crm_links_to_service_jobs.php` | Created | Adds `enquiry_id` and `deal_id` (nullable FKs) to `service_jobs` |
| `app/Models/Work/ServiceJob.php` | Extended | Added `enquiry()`, `deal()` BelongsTo; `scopeForEnquiry`, `scopeForDeal` |
| `app/Models/Crm/Enquiry.php` | Extended + fixed | Added `serviceJobs()` HasMany; fixed pre-existing syntax bug (missing `}`) |
| `app/Models/Crm/Deal.php` | Extended | Added `serviceJobs()` HasMany |
| `app/Events/Crm/ServiceJobCreatedFromEnquiry.php` | Created | `service_job_created_from_lead` signal |
| `app/Events/Crm/ServiceJobCreatedFromDeal.php` | Created | `service_job_created_from_opportunity` signal |
| `app/Events/Crm/ServiceJobClosedUpdatesCrm.php` | Created | `service_job_closed_updates_crm` signal |
| `app/Events/Crm/CrmFollowUpActivityCreated.php` | Created | `crm_followup_activity_created` signal |
| `app/Events/Crm/CrmPipelineStageUpdatedFromService.php` | Created | `crm_pipeline_stage_updated_from_service` signal |
| `app/Services/Crm/CrmServiceJobService.php` | Created | Conversion service: enquiry→job, deal→job, job-closed→crm |
| `app/Http/Controllers/Core/Crm/EnquiryController.php` | Extended | Added `convertToServiceJob` action |
| `routes/core/crm.routes.php` | Extended | Added `POST enquiries/{enquiry}/convert-to-job` |
| `config/workcore.php` | Updated | `deals` feature flag set to `true` (module 6 operational) |

### Relationship targets wired

| ServiceJob → | Relationship | Notes |
|-------------|-------------|-------|
| Customer | `BelongsTo` | Pre-existing |
| Enquiry (Lead) | `BelongsTo` via `enquiry_id` | Added in this pass |
| Deal (Opportunity) | `BelongsTo` via `deal_id` | Added in this pass |
| Quote | `BelongsTo` | Pre-existing |
| Invoice | `BelongsTo` | Pre-existing |
| Site | `BelongsTo` | Pre-existing |
| Agreement | `BelongsTo` | Pre-existing |

### CRM lifecycle signals emitted

| Signal | Trigger |
|--------|---------|
| `ServiceJobCreatedFromEnquiry` | `CrmServiceJobService::createJobFromEnquiry()` |
| `ServiceJobCreatedFromDeal` | `CrmServiceJobService::createJobFromDeal()` |
| `ServiceJobClosedUpdatesCrm` | `CrmServiceJobService::notifyCrmJobClosed()` |
| `CrmFollowUpActivityCreated` | Available for listeners on follow-up activity creation |
| `CrmPipelineStageUpdatedFromService` | Available for listeners on job stage change |

### Tenancy
All new fields and conversions are scoped to `company_id`. The `CrmServiceJobService`
reads `company_id` from the source model (enquiry or deal) and propagates it to the job.

### No duplicate CRM created
- No new CRM models, pipelines, or lead/opportunity tables were introduced.
- The host `Enquiry` and `Deal` models were extended in place.
- All conversions bridge through existing host structures.

### Deferred
- Deal `convertToServiceJob` controller action: DealController currently uses demo data
  (`WorkcoreDemoData`); a proper conversion endpoint requires that controller to be wired
  to the real `Deal` model first (future pass).
- CRM pipeline stage listener wiring: `CrmPipelineStageUpdatedFromService` event is
  declared; a listener connecting it to `Enquiry/Deal::stage` update is deferred to
  the listener registration pass.
- `CrmFollowUpActivityCreated` listener wiring: event is declared; listener is deferred.
