# HRM Biometric Layer

## Table: biometric_punches
Records raw punch events from any source.

| Column       | Type              | Notes                              |
|--------------|-------------------|------------------------------------|
| punch_type   | string            | clock_in/clock_out/break_start/break_end |
| punch_source | string            | device/mobile/gps/manual           |
| punched_at   | datetime          | when the punch occurred            |
| latitude     | decimal(10,7)     | GPS coordinates (nullable)         |
| longitude    | decimal(10,7)     | GPS coordinates (nullable)         |
| device_id    | string            | hardware device identifier         |
| raw_payload  | json              | full device payload for audit      |
| attendance_id| FK → attendances  | linked attendance record           |

## BiometricIngestService
- `ingestPunch(array $data): BiometricPunch` — creates punch, auto-links attendance
- `resolveAttendance(BiometricPunch $punch): ?Attendance` — creates (clock_in) or updates (clock_out) attendance

## Flow
clock_in punch → create Attendance (status=checked_in) → link punch.attendance_id
clock_out punch → find open Attendance → update check_out_at → link punch.attendance_id
