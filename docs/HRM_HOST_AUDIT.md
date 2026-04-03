# HRM Host Audit

## Existing HRM-Related Models

| Model | Namespace | Table | Key Fields |
|-------|-----------|-------|------------|
| WeeklyTimesheet | App\Models\Work | weekly_timesheets | company_id, user_id, week_start, week_end, total_hours, status, approved_by, approved_at |
| Timelog | App\Models\Work | timelogs | company_id, user_id, started_at, ended_at, duration_minutes |
| Attendance | App\Models\Work | attendances | company_id, user_id, service_job_id, check_in_at, check_out_at, status |
| Shift | App\Models\Work | shifts | company_id, user_id, service_job_id, start_at, end_at, status |
| Leave | App\Models\Work | leaves | company_id, user_id, type, start_date, end_date |
| LeaveHistory | App\Models\Work | leave_histories | leave_id, action, performed_by |
| LeaveQuota | App\Models\Work | leave_quotas | company_id, user_id, type, allocated_days, used_days |

## Existing Controllers

| Controller | Namespace | Routes |
|-----------|-----------|--------|
| WeeklyTimesheetController | App\Http\Controllers\Core\Team | dashboard.team.timesheets.* |
| AttendanceController | App\Http\Controllers\Core\Work | dashboard.work.attendance.* |
| LeaveController | App\Http\Controllers\Core\Work | dashboard.work.leaves.* |
| ShiftController | App\Http\Controllers\Core\Work | dashboard.work.shifts.* |
| TimelogController | App\Http\Controllers\Core\Work | dashboard.work.timelogs.* |

## Existing Events (App\Events\Work)

- JobCompleted, JobStarted, JobCancelled, JobAssigned, JobStageChanged, JobMarkedBillable

## Existing Policies (App\Policies)

- InvoicePolicy, QuotePolicy, ExpensePolicy, PaymentPolicy, ModelPolicy, UserSupportPolicy

## Concerns / Traits

- BelongsToCompany — global company scope, company() relation, scopeForCompany()
- HasFactory — standard Laravel factory support

## Known Issues

- WeeklyTimesheetController uses WorkcoreDemoData (stub) — not real DB
- No TimesheetSubmission model or migration exists
- No StaffProfile model or migration exists
- No HRM events (TimesheetSubmitted/Approved/Rejected) exist
- No TimesheetPolicy exists
- No TimesheetService exists

## Route Files

- routes/core/team.routes.php — timesheets index/show/submit/approve/reject already registered
- routes/core/work.routes.php — attendance/leave/shift/timelog routes
