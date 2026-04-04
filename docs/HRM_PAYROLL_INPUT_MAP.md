# HRM Payroll Input Map

## PayrollInputService Methods

### calculateWeeklyHours(userId, weekStart, weekEnd): float
Sums `duration_minutes` from Attendance records with check_in within range.
Returns total hours worked.

### calculateOvertime(userId, weekStart, weekEnd, standard=40.0): float
Returns max(0, workedHours - standardHoursPerWeek).

### calculateLeaveHours(userId, periodStart, periodEnd, hoursPerDay=8.0): float
Counts approved leave days overlapping the period × hoursPerDay.

### calculatePayableHours(userId, periodStart, periodEnd): array
Returns: `['regular' => float, 'overtime' => float, 'leave' => float, 'total' => float]`

## Integration Points
- Attendance.duration_minutes (set by Attendance model booted hook)
- Leave.status = 'approved' + date range overlap
- Result feeds downstream payroll export (not yet implemented)
