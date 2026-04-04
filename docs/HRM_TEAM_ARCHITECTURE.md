# HRM Team Architecture

## Domain Structure
```
App\Models\Work\
  StaffProfile          — employee record, links User → Department
  Department            — company org unit with parent/child hierarchy
  EmploymentLifecycleState — audit trail for status changes
  Shift                 — scheduled work period
  ShiftAssignment       — assignment of shift to user
  Attendance            — clock-in/out record
  BiometricPunch        — raw punch event (device/mobile/gps/manual)
  Leave                 — leave request with approval workflow
  LeaveHistory          — audit trail for leave changes
  LeaveQuota            — allowance tracking

App\Services\HRM\
  TimesheetService      — weekly timesheet submission/approval
  BiometricIngestService — ingest punch events, link to attendance
  PayrollInputService   — calculate hours/overtime/leave for payroll

App\Events\Work\ (HRM signals)
  ShiftAssigned
  LeaveApproved / LeaveRejected
  EmployeeStatusChanged
  DepartmentAssigned
  TimesheetSubmitted / TimesheetApproved / TimesheetRejected
```

## Key Relationships
- User → hasOne StaffProfile
- User → hasMany directReportProfiles (via StaffProfile.manager_id)
- StaffProfile → belongsTo Department
- Department → hasMany children (self-ref parent_id)
- Shift → hasMany ShiftAssignments
- BiometricPunch → belongsTo Attendance
