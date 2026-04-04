# HRM Employment Lifecycle

## Table: employment_lifecycle_states
Audit trail for every employee status change.

| Column          | Notes                                  |
|-----------------|----------------------------------------|
| user_id         | FK → users                             |
| staff_profile_id| FK → staff_profiles                    |
| status          | new status (active/suspended/terminated/etc) |
| previous_status | what it was before                     |
| changed_by      | FK → users (admin who made the change) |
| effective_at    | when the change takes effect           |
| notes           | free text explanation                  |

## EmployeeStatusChanged Event
Dispatched with the new `EmploymentLifecycleState` and `previousStatus` string.
Listeners can trigger notifications, access revocation, payroll stop, etc.

## StaffProfile.employment_status
Direct field on staff_profiles for current status.
EmploymentLifecycleState records the full history.
