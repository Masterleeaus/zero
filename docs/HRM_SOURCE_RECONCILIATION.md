# HRM Pass 2 — Source Reconciliation

## Pass 1 Foundation
- Models: StaffProfile, WeeklyTimesheet, TimesheetSubmission, Shift, Leave, LeaveHistory, LeaveQuota, Attendance
- Services: TimesheetService
- Events: TimesheetSubmitted, TimesheetApproved, TimesheetRejected
- Policy: TimesheetPolicy
- Controllers: StaffProfileController, WeeklyTimesheetController

## Pass 2 Additions
- Models: Department, EmploymentLifecycleState, ShiftAssignment, BiometricPunch
- Services: BiometricIngestService, PayrollInputService
- Events: ShiftAssigned, LeaveApproved, LeaveRejected, EmployeeStatusChanged, DepartmentAssigned
- Policies: StaffProfilePolicy, DepartmentPolicy, ShiftPolicy, LeavePolicy
- Controllers: DepartmentController + LeaveController approve/reject

## Schema Alterations
- staff_profiles: +department_id, +employment_status
- leaves: +approved_by, +approved_at, +rejection_reason
- shifts: +shift_type, +recurring_days, +location_id, +is_published
