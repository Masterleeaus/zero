# ADMIN PROVIDER BINDINGS

**Phase 5 — Service Provider Registration**
Generated: 2026-04-03

---

## Provider

**Class:** `App\Providers\AdminServiceProvider`
**File:** `app/Providers/AdminServiceProvider.php`
**Registered in:** `config/app.php` (providers array)

---

## Singleton Bindings

| Binding | Class | Notes |
|---|---|---|
| `AdminUserService` | `App\Services\Admin\AdminUserService` | User CRUD + role assignment |
| `AdminRoleService` | `App\Services\Admin\AdminRoleService` | Spatie role/permission management |
| `AdminSettingsService` | `App\Services\Admin\AdminSettingsService` | settings/settings_two tables |
| `AdminAuditService` | `App\Services\Admin\AdminAuditService` | tz_audit_log reader |

---

## Config Merge

```php
$this->mergeConfigFrom(base_path('config/admin.php'), 'admin');
```

Config keys available via `config('admin.*')`:

| Key | Default | Description |
|---|---|---|
| `admin.audit.per_page` | 50 | Audit log pagination size |
| `admin.audit.retention_days` | 365 | Planned retention (future purge job) |
| `admin.users.per_page` | 25 | User list pagination size |

---

## Route Loading

Routes are **not** loaded by the provider directly.
`RouteServiceProvider` automatically discovers `routes/core/admin.routes.php`
via its glob pattern: `routes/core/*.routes.php`.

---

## View Resolution

Views resolve under the standard Blade `panel.admin.*` namespace:

| View Key | File |
|---|---|
| `panel.admin.roles.index` | `resources/views/default/panel/admin/roles/index.blade.php` |
| `panel.admin.audit.index` | `resources/views/default/panel/admin/audit/index.blade.php` |
| `panel.admin.users.index` | `resources/views/default/panel/admin/users/index.blade.php` (pre-existing) |
| `panel.admin.settings.general` | `resources/views/default/panel/admin/settings/general.blade.php` (pre-existing) |

---

## Support Helpers

`App\Support\Admin\AdminHelpers` provides:

- `AdminHelpers::isAdminUser()` — check if current user has admin/super_admin role
- `AdminHelpers::permissionLabel()` — human-readable permission name
- `AdminHelpers::adminPrefix()` — base admin URL prefix
