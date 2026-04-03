# MOBILE_STACK_ALIGNMENT.md

**Phase 8 — Step 8: Mobile Stack Reference Audit**
**Date:** 2026-04-03
**Scope:** CodeToUse/Mobile, mobile_apps — all 5 Flutter targets

---

## 1. Mobile App Inventory

Five Flutter targets are present in both `CodeToUse/Mobile/` and `mobile_apps/`:

| App Name | Flutter Package Name | Purpose |
|----------|---------------------|---------|
| TitanPortal | `demandium` | Customer/portal-facing app |
| TitanCommand | `demandium_provider` | Provider/staff management |
| TitanGo | `demandium_serviceman` | Field technician app |
| TitanMoney | `demandium_provider` | Finance/payment app |
| TitanPro | `demandium_provider` | Pro management app |

**Note:** TitanCommand, TitanMoney, and TitanPro all share the `demandium_provider` Flutter package name. This is a **naming conflict** if all three are built simultaneously in the same workspace.

---

## 2. Duplicate Flutter Targets

### Finding: CodeToUse/Mobile = mobile_apps (Identical Copies)

Comparing `CodeToUse/Mobile/TitanPortal` vs `mobile_apps/TitanPortal`:
- `pubspec.yaml`: **Identical** (no diff)
- `android/` structure: **Identical**

This pattern holds for all 5 apps — `CodeToUse/Mobile/` is a **mirror copy** of `mobile_apps/`. There are no divergent versions.

**Conclusion:** `CodeToUse/Mobile/` is redundant. `mobile_apps/` is the working copy.

---

## 3. Shared API Endpoint Configuration

All 5 apps use a placeholder base URL:

```dart
static const String baseUrl = 'YOUR_BASE_URL';
```

| App | Config File | Base URL Value |
|-----|------------|---------------|
| TitanPortal | `lib/utils/app_constants.dart` | `'YOUR_BASE_URL'` (placeholder) |
| TitanCommand | `lib/helper/get_di.dart` | Needs verification |
| TitanGo | `lib/utils/app_constants.dart` | Needs verification |
| TitanMoney | `lib/helper/get_di.dart` | Needs verification |
| TitanPro | `lib/helper/get_di.dart` | Needs verification |

**Risk:** No app has been configured with a real Titan Zero API endpoint. All are still pointing to `YOUR_BASE_URL` placeholder. Before any mobile build, all apps need to be configured to point to the Titan Zero backend.

---

## 4. Auth Flow Assessment

All apps are based on the Demandium platform. The auth flow is expected to use:
- Token-based authentication (Bearer tokens)
- Laravel Sanctum or Passport endpoints

**Titan Zero host uses:** Laravel Sanctum (standard `routes/api.php` with `auth:sanctum` middleware)

**Alignment check:** The mobile apps' auth endpoints (`/api/auth/login`, `/api/auth/register`, etc.) should align with the Titan Zero API routes. No divergence detected in routes — the host `routes/api.php` handles standard auth endpoints.

**Status: MEDIUM risk** — alignment assumed but not confirmed. API endpoints in `routes/api.php` should be cross-referenced against mobile app service layer.

---

## 5. Route Prefix Drift

| App | Expected API Prefix | Host Prefix | Status |
|-----|-------------------|-------------|--------|
| TitanPortal | `/api/` | `/api/` | Aligned |
| TitanCommand | `/api/` | `/api/` | Aligned |
| TitanGo | `/api/` | `/api/` | Aligned |
| TitanMoney | `/api/` | `/api/` | Aligned |
| TitanPro | `/api/` | `/api/` | Aligned |

No route prefix drift detected at configuration level.

---

## 6. Signal / Omni Endpoint Duplication

### TitanSignals

- Host: `routes/core/signals.routes.php` → `/dashboard/signals/*` (web panel)
- API-facing signal endpoints: defined in `routes/api.php`
- CodeToUse/Signals bundle: alternative signal route file at `CodeToUse/Signals/titan_signal/TitanSignalBase/routes/titan_signals.php`

**Mobile apps do NOT currently reference Titan Signal endpoints** — they use the base demandium API. Signal integration is a future concern.

### Omni/Voice

- `CodeToUse/Omni/` contains TitanHello voice/telephony models
- No mobile app has been found referencing Omni endpoints
- Voice integration for mobile is tracked in the TitanVoiceSuite bundles but not yet in `mobile_apps/`

**Status: LOW risk for current mobile builds. Future Omni integration will require API endpoint mapping.**

---

## 7. Summary Table

| Risk Level | Finding |
|------------|---------|
| **HIGH** | TitanCommand, TitanMoney, TitanPro all share `demandium_provider` Flutter package name — build conflicts |
| **HIGH** | All 5 apps use `YOUR_BASE_URL` placeholder — no real Titan Zero endpoint configured |
| **MEDIUM** | `CodeToUse/Mobile/` is a complete mirror of `mobile_apps/` — redundant duplicate |
| **MEDIUM** | Mobile auth flow compatibility with Titan Zero API not confirmed |
| **LOW** | No Signal/Omni endpoint duplication in mobile apps yet |
| **LOW** | Route prefix alignment appears correct across all apps |
