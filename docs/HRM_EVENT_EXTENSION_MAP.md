# HRM Event Extension Map

## Pass 2 Events (registered in EventServiceProvider)
| Event                  | Payload                              | Module    |
|------------------------|--------------------------------------|-----------|
| ShiftAssigned          | ShiftAssignment                      | Scheduling|
| LeaveApproved          | Leave, User(approver)                | HR        |
| LeaveRejected          | Leave, User(rejector), string reason | HR        |
| EmployeeStatusChanged  | EmploymentLifecycleState, string prev| Lifecycle |
| DepartmentAssigned     | StaffProfile, Department             | Org       |

## Registration
All registered in `App\Providers\EventServiceProvider::$listen` under MODULE HRM_PASS2 comment.
No auto-discovery (`shouldDiscoverEvents()` returns false).
Listeners array is empty — wired when notification/automation listeners are added.
