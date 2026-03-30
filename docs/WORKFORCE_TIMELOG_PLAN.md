# Workforce & Timelog Plan

## Current slice absorbed
- Timelog table with `company_id`, `user_id`, `service_job_id`, `started_at`, `ended_at`, `duration_minutes`, `notes`.
- Timelog model uses `BelongsToCompany` and auto-fills duration; controller/views for list/create/stop under `/dashboard/work/timelogs`.
- Routes protected by auth + company scope.

## Next steps
- Add check-in/out shortcut buttons from service job view.
- Add per-day rollups and overtime flags.
- Add attendance/shift models if present in WorkCore migrations.
- Export/summary in Insights once real data exists.
