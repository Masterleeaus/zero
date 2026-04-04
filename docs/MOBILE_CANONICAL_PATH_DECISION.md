# MOBILE_CANONICAL_PATH_DECISION.md

**Phase 9 — Step 7: Mobile Canonical Path Decision**
**Date:** 2026-04-04
**Pass:** Phase 9 — Critical Structural Stabilisation

---

## Decision

**`mobile_apps/` is the canonical path for active mobile development.**

`CodeToUse/Mobile/` is a mirror copy and is designated **reference-only**. It must not be maintained in parallel.

---

## Evidence

Both `CodeToUse/Mobile/` and `mobile_apps/` contain identical Flutter projects:

| App | Flutter Package | Status |
|-----|----------------|--------|
| TitanPortal | `demandium` | Identical in both paths |
| TitanCommand | `demandium_provider` | Identical in both paths |
| TitanGo | `demandium_serviceman` | Identical in both paths |
| TitanMoney | `demandium_provider` | Identical in both paths |
| TitanPro | `demandium_provider` | Identical in both paths |

`pubspec.yaml` files were confirmed identical between the two paths (no version divergence). This means `CodeToUse/Mobile/` was at some point a snapshot or copy of `mobile_apps/`.

---

## Canonical Path: `mobile_apps/`

**Rationale:**
- `mobile_apps/` sits at the root of the host repository alongside `app/`, `routes/`, `database/` — the standard Laravel project layout
- `CodeToUse/` is the designated source archive and integration staging area, not a development workspace
- Future mobile builds, CI/CD pipelines, and development should reference `mobile_apps/` only

---

## Reference-Only Path: `CodeToUse/Mobile/`

`CodeToUse/Mobile/` is retained as a snapshot reference:
- It may be useful for diffing against `mobile_apps/` to identify any drift introduced during integration
- It should NOT be modified
- It should NOT be used as a build source

**No files are deleted from `CodeToUse/Mobile/` in this pass** — content is preserved per quarantine policy.

---

## Known Issues Requiring Future Action

| Issue | App | Status |
|-------|-----|--------|
| All 5 apps use `'YOUR_BASE_URL'` placeholder | All | Must be configured before any mobile build |
| TitanCommand, TitanMoney, and TitanPro share the `demandium_provider` Flutter package name | These three | Naming conflict in shared workspace — must resolve before simultaneous multi-app builds |
| Auth flow has not been verified against Titan Zero JWT/Sanctum endpoints | All | Must be aligned before mobile-backend integration |

---

## Recommended Next Steps (Mobile — Post Phase 9)

1. Configure `baseUrl` in all 5 apps to point to the Titan Zero API endpoint
2. Resolve `demandium_provider` package name collision between TitanCommand, TitanMoney, TitanPro
3. Verify auth flow against Laravel Passport/Sanctum token endpoints
4. Remove `CodeToUse/Mobile/` snapshot after confirming `mobile_apps/` is the active source
