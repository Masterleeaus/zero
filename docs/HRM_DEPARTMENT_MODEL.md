# HRM Department Model

## Table: departments
| Column    | Notes                                   |
|-----------|-----------------------------------------|
| company_id| scoped to company                       |
| name      | department name                         |
| code      | optional short code                     |
| parent_id | self-referencing (no FK constraint)     |
| status    | active/inactive                         |

## Hierarchy
- `parent()` BelongsTo self via parent_id
- `children()` HasMany self via parent_id
- Supports unlimited nesting; no FK constraint to allow flexible management

## Links
- `staffProfiles()` HasMany StaffProfile via department_id
- StaffProfile.department_id added in Pass 2 migration

## DepartmentPolicy
- viewAny: company_id not null
- view: company match
- create/update/delete: isAdmin() + company match

## Routes
Registered as Route::resource('departments') under dashboard.team. prefix.
