# Security Domain — Pass Implementation Report

**Stage 8 output for the Security Integration Pass**

---

## Summary

The Security domain has been extracted from `CodeToUse/PWA/platform/CyberSecurity/`
and integrated into the Titan Zero host following the reuse → extend → refactor
→ repair → replace doctrine.

All 5 extracted tables are new (no host conflicts).  All 4 middleware classes are
new and registered as named aliases.  The existing `AuditTrail` (AI/signals) was
**not** modified; a separate tenant-aware `SecurityAuditService` was created for
the security domain.

---

## Files Edited

### Modified
| File | Change |
|---|---|
| `app/Http/Kernel.php` | Added 4 middleware aliases: `security.blacklist_ip`, `security.blacklist_email`, `security.login_expiry`, `security.cyber` |

---

## Files Created

### Models (`app/Models/Security/`)
| File | Table | Tenant? |
|---|---|---|
| `CyberSecurityConfig.php` | `cyber_securities` | System-wide (singleton) |
| `BlacklistIp.php` | `blacklist_ips` | System-wide |
| `BlacklistEmail.php` | `blacklist_emails` | System-wide |
| `LoginExpiry.php` | `login_expiries` | User-scoped |
| `SecurityAuditEvent.php` | `security_audit_events` | ✅ `BelongsToCompany` |

### Services (`app/Services/Security/`)
| File | Purpose |
|---|---|
| `SecurityAuditService.php` | Tenant-aware audit event recording + querying |
| `CyberSecurityConfigService.php` | Config management, blacklist CRUD helpers |

### Middleware (`app/Http/Middleware/Security/`)
| File | Alias | Purpose |
|---|---|---|
| `BlackListIpMiddleware.php` | `security.blacklist_ip` | Block requests from blacklisted IPs |
| `BlackListEmailMiddleware.php` | `security.blacklist_email` | Block requests with blacklisted email |
| `LoginExpiryMiddleware.php` | `security.login_expiry` | Force logout on expired accounts |
| `CyberSecurityMiddleware.php` | `security.cyber` | Login rate-limiting + progressive lockout + unique session |

### Events (`app/Events/Security/`)
| File | Purpose |
|---|---|
| `LoginLockoutEvent.php` | Fired when lockout count reaches alert threshold |

### Controllers (`app/Http/Controllers/Core/Security/`)
| File | Routes |
|---|---|
| `SecuritySettingsController.php` | `dashboard.security.settings.*`, `dashboard.security.audit.*` |
| `BlacklistIpController.php` | `dashboard.security.blacklist.ips.*` |
| `BlacklistEmailController.php` | `dashboard.security.blacklist.emails.*` |

### Routes
| File | Prefix | Route names |
|---|---|---|
| `routes/core/security.routes.php` | `/dashboard/security` | `dashboard.security.*` |

### Migration
| File | Tables |
|---|---|
| `database/migrations/2026_04_03_800100_create_security_domain_tables.php` | `cyber_securities`, `blacklist_ips`, `blacklist_emails`, `login_expiries`, `security_audit_events` |

### Tests
| File | Coverage |
|---|---|
| `tests/Feature/Security/SecurityDomainTest.php` | 16 test cases |

### Documentation
| File | Stage |
|---|---|
| `docs/SECURITY_HOST_AUDIT.md` | Stage 1 |
| `docs/SECURITY_SOURCE_AUDIT.md` | Stage 2 |
| `docs/SECURITY_OVERLAP_MATRIX.md` | Stage 3 |
| `docs/SECURITY_TENANCY_ALIGNMENT.md` | Stage 4 |
| `docs/SECURITY_CONNECTION_MAP.md` | Stage 5 |
| `docs/SECURITY_EXTRACTION_PLAN.md` | Stage 6 |
| `docs/SECURITY_PASS_IMPLEMENTATION_REPORT.md` | Stage 8 (this file) |

---

## Route Summary

```
GET    /dashboard/security/settings                → SecuritySettingsController@index
PUT    /dashboard/security/settings/login-protection → SecuritySettingsController@updateLoginProtection
PUT    /dashboard/security/settings/session-policy  → SecuritySettingsController@updateSessionPolicy
GET    /dashboard/security/audit                   → SecuritySettingsController@auditTrail
GET    /dashboard/security/blacklist/ips           → BlacklistIpController@index
POST   /dashboard/security/blacklist/ips           → BlacklistIpController@store
PUT    /dashboard/security/blacklist/ips/{id}      → BlacklistIpController@update
DELETE /dashboard/security/blacklist/ips/{id}      → BlacklistIpController@destroy
GET    /dashboard/security/blacklist/emails        → BlacklistEmailController@index
POST   /dashboard/security/blacklist/emails        → BlacklistEmailController@store
PUT    /dashboard/security/blacklist/emails/{id}   → BlacklistEmailController@update
DELETE /dashboard/security/blacklist/emails/{id}   → BlacklistEmailController@destroy
```

All routes are behind `auth` + `throttle:120,1` + `security.blacklist_ip`.
All controller actions additionally check `is_superadmin || isAdmin()`.

---

## Test Coverage

| Test | Area |
|---|---|
| `test_cyber_security_config_singleton_is_created_on_first_access` | Model |
| `test_singleton_returns_same_row_on_repeated_calls` | Model |
| `test_blacklist_ip_can_be_stored_and_retrieved` | Model |
| `test_blacklist_email_can_be_stored_and_retrieved` | Model |
| `test_login_expiry_links_to_user` | Model |
| `test_audit_service_records_event_without_tenant` | Service |
| `test_audit_service_records_event_with_tenant` | Service |
| `test_audit_service_scopes_events_to_company` | Service / Tenant isolation |
| `test_config_service_updates_login_protection` | Service |
| `test_config_service_updates_session_policy` | Service |
| `test_config_service_detects_blacklisted_ip` | Service |
| `test_config_service_detects_blacklisted_email` | Service |
| `test_config_service_detects_blacklisted_domain` | Service |
| `test_blacklist_ip_middleware_blocks_known_ip` | Middleware |
| `test_blacklist_ip_middleware_allows_clean_ip` | Middleware |
| `test_login_expiry_middleware_logs_out_expired_user` | Middleware |
| `test_security_routes_are_registered` | Routes |
| `test_security_audit_events_are_tenant_isolated` | Tenant isolation |

---

## Infrastructure Discarded (not ported)

| Component | Reason |
|---|---|
| `CyberSecurityServiceProvider` | Host uses `Kernel.php` + `AppServiceProvider` |
| `EventServiceProvider` (module) | Host `EventServiceProvider` covers all event bindings |
| `RouteServiceProvider` (module) | Host `RouteServiceProvider` auto-loads `routes/core/*.routes.php` |
| `CyberSecuritySetting` entity | Module licence management not applicable |
| `CompanyCreatedListener` | Company seeding is host-specific |
| Views / Blade templates | Host uses its own panel theme |
| Language files | Host i18n system |
| `laraupdater.json` | Module updater not applicable |
| `xss_ignore.php` | Handled by Laravel built-in output escaping |

---

## Open Items / Phase 2 Tasks

| Item | Priority |
|---|---|
| Wire `NodeTrustService` to call `SecurityAuditService::record(TYPE_DEVICE_UNTRUSTED)` | Medium |
| Wire `SignalSignatureValidator` to call `SecurityAuditService::record(TYPE_SIGNAL_REJECTED)` | Medium |
| Implement `LockoutEmailListener` + `DifferentIpListener` for alert notifications | Low |
| Evaluate per-tenant blacklist support | Low |
| Add `company_id` to `tz_audit_log` for full AI audit tenant isolation | Low (separate pass) |
| AnomalyDetectionService on `security_audit_events` | Future |

---

## Security Rules Applied (Implementation)

✅ Extended host middleware stack (did not replace)
✅ Extended audit logging (new table, did not modify tz_audit_log)
✅ Preserved existing auth system (Sanctum, Passport, guards unchanged)
✅ No duplicate user model
✅ No duplicate roles/permissions tables
✅ No duplicate session handlers
✅ No duplicate tenancy logic (used BelongsToCompany trait)
✅ All new tables use `if (!Schema::hasTable(...))` guards
✅ Singleton config seeded with safe defaults
✅ All middleware aliases registered — opt-in by route, not forced globally
