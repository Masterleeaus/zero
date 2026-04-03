# ADMIN TABLE MAP

**Phase 3 — Database Alignment**
Generated: 2026-04-03

---

## Tables Used by Titan Admin Module

| Table | Prefix | Owner | Migration | Notes |
|---|---|---|---|---|
| `tz_audit_log` | `tz_` | TitanCore | 2026_03_30_220000 | Core signal audit trail |
| `tz_audit_log` (extended) | `tz_` | Admin | 2026_04_03_800100 | Added `company_id`, `subject_type`, `subject_id` |
| `users` | none | Core | (pre-existing) | User model |
| `roles` | none | Spatie | (spatie/laravel-permission) | Role management |
| `permissions` | none | Spatie | (spatie/laravel-permission) | Permission management |
| `model_has_roles` | none | Spatie | (spatie/laravel-permission) | User↔Role pivot |
| `model_has_permissions` | none | Spatie | (spatie/laravel-permission) | User↔Permission pivot |
| `role_has_permissions` | none | Spatie | (spatie/laravel-permission) | Role↔Permission pivot |
| `settings` | none | Core | (pre-existing) | Primary settings store |
| `settings_two` | none | Core | (pre-existing) | Extended settings store |

---

## Migration 800100 Detail

**File:** `database/migrations/2026_04_03_800100_add_company_id_to_tz_audit_log.php`

Adds to `tz_audit_log`:

| Column | Type | Notes |
|---|---|---|
| `company_id` | `unsignedBigInteger nullable` | Tenant boundary — Titan tenancy doctrine |
| `subject_type` | `string(100) nullable` | Polymorphic subject class |
| `subject_id` | `unsignedBigInteger nullable` | Polymorphic subject ID |

---

## Prefix Policy Compliance

| Table | Expected Prefix | Compliant |
|---|---|---|
| `tz_audit_log` | `tz_*` (Titan core) | ✅ |
| `users` / `roles` / `permissions` | none (core) | ✅ |
| `settings` / `settings_two` | none (core) | ✅ |

---

## Tenancy Alignment (Phase 6)

- `tz_audit_log.company_id` added via migration 800100
- `AdminAuditLog::scopeForCompany()` filters by `company_id`
- `AdminAuditService::paginate()` accepts `company_id` filter
- `users` table already has `company_id` via `BelongsToCompany` trait
