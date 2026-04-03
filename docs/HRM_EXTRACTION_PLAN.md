# HRM Extraction Plan

## Pass Scope

This pass implements the HRM foundation on top of the existing Work domain.

## Files to Create

### Models
1. `app/Models/Work/TimesheetSubmission.php`
2. `app/Models/Work/StaffProfile.php`

### Migrations
3. `database/migrations/2026_04_03_700100_create_timesheet_submissions_table.php`
4. `database/migrations/2026_04_03_700200_create_staff_profiles_table.php`

### Events
5. `app/Events/Work/TimesheetSubmitted.php`
6. `app/Events/Work/TimesheetApproved.php`
7. `app/Events/Work/TimesheetRejected.php`

### Policy
8. `app/Policies/TimesheetPolicy.php`

### Service
9. `app/Services/HRM/TimesheetService.php`

### Controllers
10. `app/Http/Controllers/Core/Team/StaffProfileController.php`

### Tests
11. `tests/Feature/TimesheetSubmissionTest.php`
12. `tests/Feature/StaffProfileTest.php`

## Files to Modify

1. `app/Http/Controllers/Core/Team/WeeklyTimesheetController.php` — replace stub with real DB queries
2. `routes/core/team.routes.php` — add staff-profiles CRUD routes

## Deferred Items

- TimesheetTimer model/table (no host timer UI exists)
- Blade views for staff-profiles (stub views acceptable for this pass)
- TimesheetSubmission detailed workflow UI
- Listener implementations for timesheet events

## Execution Order

1. Migrations (schema first)
2. Models (depend on schema)
3. Events (no dependencies)
4. Policy (depends on models)
5. Service (depends on models + events)
6. Fix WeeklyTimesheetController (depends on models + service)
7. StaffProfileController (depends on models)
8. Routes (depends on controllers)
9. Tests (depends on all above)
10. Documentation
