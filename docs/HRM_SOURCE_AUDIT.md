# HRM Source Audit (HRM.zip)

## Source Location

`/home/runner/work/zero/zero/CodeToUse/HRM.zip`

## Contents Overview

The HRM.zip contains a Timesheet module only. No full Entity models were included in the zip.

### Directories Found

- `Events/` — Timesheet lifecycle events
- `Listeners/` — Event listener stubs
- `Policies/` — Timesheet access policies
- `Routes/` — Module route definitions

### Referenced Entities (Not Present in ZIP)

| Entity | Purpose |
|--------|---------|
| Timesheet | Weekly timesheet record per user |
| TimesheetSubmission | Submission record with review workflow |
| TimesheetTimer | Active timer tracking (not implemented in this pass) |
| TimesheetUtility | Helper/utility class for timesheet calculations |

### Key Patterns from Source

- Events carry model instances as readonly constructor properties
- Policies use company_id scoping for all authorization
- Routes follow resource conventions with additional action routes (submit, approve, reject)

## Scope Decision

The zip does not provide production-ready Entity models. The host already has:
- `WeeklyTimesheet` — primary timesheet record
- `Timelog` — time entries (provides duration data)

This pass will:
1. Build `TimesheetSubmission` as a submission/review workflow record
2. Build `StaffProfile` as HRM foundation
3. Wire events, policy, and service around `WeeklyTimesheet`
4. NOT implement TimesheetTimer (deferred — no host timer UI exists)
