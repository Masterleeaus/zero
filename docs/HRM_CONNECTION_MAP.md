# HRM Connection Map

## New Models

### TimesheetSubmission
- **Table**: `timesheet_submissions`
- **Connects to**: `users` (user_id, reviewed_by), `companies` (company_id), `timelogs` (via user_id + date range)
- **Routes**: `dashboard.team.timesheets.*` (submission sub-actions)
- **Events**: `TimesheetSubmitted`, `TimesheetApproved`, `TimesheetRejected`
- **Policy**: `TimesheetPolicy`
- **Service**: `TimesheetService`

### StaffProfile
- **Table**: `staff_profiles`
- **Connects to**: `users` (user_id, manager_id), `companies` (company_id)
- **Routes**: `dashboard.team.staff-profiles.*`
- **Controller**: `StaffProfileController`

## Service Wiring

### TimesheetService
- Depends on: `WeeklyTimesheet`, `Timelog`, `User`
- Fires: `TimesheetSubmitted`, `TimesheetApproved`, `TimesheetRejected`
- Called by: `WeeklyTimesheetController` (submit/approve/reject actions)

## Event Flow

```
User submits timesheet
  → WeeklyTimesheetController::submit()
  → TimesheetService::submitWeeklyTimesheet()
  → WeeklyTimesheet status = 'submitted'
  → TimesheetSubmitted::dispatch($sheet)

Manager approves
  → WeeklyTimesheetController::approve()
  → TimesheetService::approveTimesheet()
  → WeeklyTimesheet status = 'approved', approved_by, approved_at set
  → TimesheetApproved::dispatch($sheet, $reviewer)

Manager rejects
  → WeeklyTimesheetController::reject()
  → TimesheetService::rejectTimesheet()
  → WeeklyTimesheet status = 'rejected'
  → TimesheetRejected::dispatch($sheet, $reviewer)
```

## UI Surfaces

- `default.panel.user.team.timesheets.index` — timesheet list
- `default.panel.user.team.timesheets.show` — timesheet detail
- `default.panel.user.team.staff-profiles.*` — staff profile views (stub)

## Provider Integration

- No new service provider needed — TimesheetService is instantiated directly
- Policy registered via `AuthServiceProvider` (existing convention)
