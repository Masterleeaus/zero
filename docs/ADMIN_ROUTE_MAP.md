# ADMIN ROUTE MAP

**Phase 8 — Output Report**
Generated: 2026-04-03

---

## Route File

`routes/core/admin.routes.php`

Auto-loaded by `RouteServiceProvider` (scans `routes/core/*.routes.php`).

---

## Route Table

| Method | URI | Name | Controller | Action |
|---|---|---|---|---|
| GET | `/dashboard/admin/users` | `titan.admin.users.index` | `AdminUserController` | `index` |
| GET | `/dashboard/admin/users/create` | `titan.admin.users.create` | `AdminUserController` | `create` |
| POST | `/dashboard/admin/users` | `titan.admin.users.store` | `AdminUserController` | `store` |
| GET | `/dashboard/admin/users/{user}/edit` | `titan.admin.users.edit` | `AdminUserController` | `edit` |
| PUT | `/dashboard/admin/users/{user}` | `titan.admin.users.update` | `AdminUserController` | `update` |
| DELETE | `/dashboard/admin/users/{user}` | `titan.admin.users.destroy` | `AdminUserController` | `destroy` |
| GET | `/dashboard/admin/roles` | `titan.admin.roles.index` | `AdminRoleController` | `index` |
| POST | `/dashboard/admin/roles` | `titan.admin.roles.store` | `AdminRoleController` | `store` |
| PUT | `/dashboard/admin/roles/{role}` | `titan.admin.roles.update` | `AdminRoleController` | `update` |
| DELETE | `/dashboard/admin/roles/{role}` | `titan.admin.roles.destroy` | `AdminRoleController` | `destroy` |
| GET | `/dashboard/admin/settings` | `titan.admin.settings.index` | `AdminSettingsController` | `index` |
| POST | `/dashboard/admin/settings` | `titan.admin.settings.update` | `AdminSettingsController` | `update` |
| GET | `/dashboard/admin/audit` | `titan.admin.audit.index` | `AdminAuditLogController` | `index` |

---

## Middleware Stack

All routes in `admin.routes.php` are protected by:

```
auth → admin → updateUserActivity
```

- `auth` — Laravel authentication guard
- `admin` — Titan admin/super_admin role check (`AdminPermissionMiddleware`)
- `updateUserActivity` — Records last activity timestamp

---

## Naming Convention

All routes use prefix `titan.admin.*` to avoid collision with the existing
`admin.titan.core.*` prefix used in `titan_admin.routes.php`.
