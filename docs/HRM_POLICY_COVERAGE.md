# HRM Policy Coverage

## Policies Registered (AuthServiceProvider)
| Model         | Policy               | Notes                              |
|---------------|----------------------|------------------------------------|
| StaffProfile  | StaffProfilePolicy   | self-edit + admin full access      |
| Department    | DepartmentPolicy     | admin create/update/delete         |
| Shift         | ShiftPolicy          | admin create/update/delete         |
| Leave         | LeavePolicy          | self-create, admin approve/reject  |
| WeeklyTimesheet | TimesheetPolicy    | registered in Pass 1               |

## Policy Details

### StaffProfilePolicy
- viewAny: company_id not null
- view: company match
- create: isAdmin()
- update: isAdmin() OR own profile (user_id match)
- delete: isAdmin() + company match

### DepartmentPolicy
- viewAny: company_id not null
- view: company match
- create/update/delete: isAdmin() + company match

### ShiftPolicy
- viewAny: company_id not null
- view: company match
- create/update: isAdmin() + company match
- delete: isAdmin() + company match

### LeavePolicy
- viewAny: company_id not null
- view: company match
- create: company_id not null
- update: company match + (isAdmin() OR own leave)
- approve/reject: isAdmin() + company match
