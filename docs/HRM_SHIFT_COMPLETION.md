# HRM Shift Completion

## Shift Model Extensions (Pass 2)
- `shift_type` (string, default 'standard') — standard|rotating|on-call
- `recurring_days` (json) — array of weekday numbers [1-7]
- `location_id` (unsignedBigInteger nullable) — site/zone reference
- `is_published` (boolean, default false) — draft/published flag

## Shift Assignment
Table: `shift_assignments`
- links shift_id → shift
- links user_id → user (assignee)
- links assigned_by → user (admin who assigned)
- status: assigned|accepted|declined|cancelled

## ShiftAssigned Event
Fired when ShiftAssignment record is created.
Listeners can notify the assigned user.
