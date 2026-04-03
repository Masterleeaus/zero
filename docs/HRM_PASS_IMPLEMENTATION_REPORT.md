# HRM Pass Implementation Report

## Summary

The HRM pass has been implemented successfully. All files have been created and verified for PHP syntax correctness.

## Files Created

### Models
- `app/Models/Work/TimesheetSubmission.php` — Submission/review workflow record with company scoping
- `app/Models/Work/StaffProfile.php` — HRM staff profile with employment details, rates, manager hierarchy

### Migrations
- `database/migrations/2026_04_03_700100_create_timesheet_submissions_table.php`
- `database/migrations/2026_04_03_700200_create_staff_profiles_table.php`

### Events
- `app/Events/Work/TimesheetSubmitted.php`
- `app/Events/Work/TimesheetApproved.php`
- `app/Events/Work/TimesheetRejected.php`

### Policy
- `app/Policies/TimesheetPolicy.php` — viewAny/view/update/submit/approve/reject using host isAdmin() pattern

### Service
- `app/Services/HRM/TimesheetService.php` — submitWeeklyTimesheet, approveTimesheet, rejectTimesheet, calculateWeekHours

### Controllers
- `app/Http/Controllers/Core/Team/StaffProfileController.php` — Full CRUD with company_id guards

### Tests
- `tests/Feature/TimesheetSubmissionTest.php`
- `tests/Feature/StaffProfileTest.php`

## Files Modified

### `app/Http/Controllers/Core/Team/WeeklyTimesheetController.php`
- Replaced WorkcoreDemoData stub with real DB queries against WeeklyTimesheet
- index(): queries with BelongsToCompany global scope, paginates 15, filters by status
- show(): model binding with company_id guard, loads user + timelogs
- submit(): delegates to TimesheetService::submitWeeklyTimesheet
- approve(): guards isAdmin(), delegates to TimesheetService::approveTimesheet
- reject(): guards isAdmin(), delegates to TimesheetService::rejectTimesheet

### `routes/core/team.routes.php`
- Added staff-profiles CRUD routes (index/create/store/show/edit/update/destroy)

## Architecture Decisions

1. **TimesheetService pattern** — stateless service class, no constructor injection of repositories. Follows existing host service patterns.
2. **Policy uses host isAdmin()** — no custom role enum needed; adapts to Roles::ADMIN | Roles::SUPER_ADMIN automatically.
3. **WeeklyTimesheet remains primary record** — TimesheetSubmission is an audit/review workflow record, not a replacement.
4. **BelongsToCompany global scope** — both new models use the trait for automatic company scoping.
5. **calculateWeekHours uses withoutGlobalScope** — service queries timelogs cross-company for accurate calculation, scoped by user_id and date range.

## Known Deferred Items

- TimesheetTimer model (no host timer UI exists)
- Blade views for staff-profiles (stub via CoreController::placeholder())
- Listener implementations for HRM events
- AuthServiceProvider policy registration (TimesheetPolicy, future StaffProfilePolicy)
- WeeklyTimesheet factory for tests

## Test Environment Note

The CI vendor directory is missing `laravel/framework` — tests cannot execute in this environment. All test code was verified for PHP syntax. Tests follow identical patterns to `LeaveFeatureTest.php` and `AttendanceFeatureTest.php`.
