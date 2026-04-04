# HRM Domain Gap Matrix

| Feature                  | Pass 1 | Pass 2 | Notes                            |
|--------------------------|--------|--------|----------------------------------|
| Staff Profiles           | ✅     | ✅     | +department_id, +employment_status |
| Weekly Timesheets        | ✅     | -      |                                  |
| Shifts                   | ✅     | ✅     | +type, +recurring_days, +location |
| Shift Assignments        | -      | ✅     | New ShiftAssignment model        |
| Leaves                   | ✅     | ✅     | +approve/reject workflow         |
| Departments              | -      | ✅     | New Department model + controller|
| Employment Lifecycle     | -      | ✅     | EmploymentLifecycleState model   |
| Biometric Punches        | -      | ✅     | BiometricPunch + IngestService   |
| Payroll Input            | -      | ✅     | PayrollInputService              |
| Attendance               | ✅     | -      | Linked via BiometricIngestService|
