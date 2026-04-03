# HRM Overlap Matrix

## Feature vs Host Coverage

| Feature | Host Has | Source Has | Action |
|---------|----------|------------|--------|
| Weekly timesheet record | ✅ WeeklyTimesheet model | ✅ Timesheet entity ref | EXTEND host model |
| Timesheet submission workflow | ❌ | ✅ TimesheetSubmission ref | CREATE new model |
| Time entry logging | ✅ Timelog model | ❌ | REUSE host model |
| Attendance tracking | ✅ Attendance model | ❌ | REUSE host model |
| Shift management | ✅ Shift model | ❌ | REUSE host model |
| Leave management | ✅ Leave/LeaveHistory/LeaveQuota | ❌ | REUSE host model |
| Staff profiles / HR data | ❌ | ❌ (implied) | CREATE new model |
| Timesheet events | ❌ | ✅ Event stubs | CREATE new events |
| Timesheet policy | ❌ | ✅ Policy stub | CREATE new policy |
| Timesheet service logic | ❌ | ✅ (referenced) | CREATE new service |
| Timer tracking | ❌ | ✅ TimesheetTimer ref | DEFER |

## Conflict Analysis

| Conflict | Resolution |
|---------|------------|
| Source 'Timesheet' vs host 'WeeklyTimesheet' | Use WeeklyTimesheet as canonical — already in DB |
| TimesheetSubmission as separate table vs status field on WeeklyTimesheet | Both useful: keep status on WeeklyTimesheet; TimesheetSubmission for review audit trail |
| Source policy checks vs host Roles enum | Adapt source policy to use host `isAdmin()` method on User |

## Safe Removals from Source

- Duplicate auth/middleware definitions (not present in this pass — zip had no app bootstrap)
- Generic provider registrations (not needed)
- Any duplicated User/Company entity definitions (not present in zip)
