# PWA Build Plan

**Agent:** Copilot  
**Purpose:** PWA build and deployment guide for Titan Zero

---

## Current PWA Status

- **Runtime version:** titan-zero-v3
- **Service Worker:** `public/sw.js`
- **Runtime JS:** `public/pwa-runtime/runtime.js`
- **IndexedDB:** v3

---

## PWA Table Prefix

All PWA tables must use `tz_pwa_*` prefix:
- `tz_pwa_ingress` — offline queue ingress
- `tz_pwa_device_capabilities` — device capability profiles
- `tz_pwa_staged_artifacts` — staged sync artifacts
- `tz_pwa_conflict_log` — conflict resolution log

---

## PWA Services

Located in `app/Services/TitanZeroPwaSystem/`:
- `PwaRuntimeContractService` — runtime contract validation
- `PwaDeferredReplayService` — deferred action replay
- `PwaQueueHealthService` — queue health monitoring
- `PwaStagingService` — artifact staging
- `NodeTrustService` — node trust validation
- `SignalSignatureValidator` — signal signature verification
- `PwaNodeFingerprint` — device fingerprinting

---

## PWA Routes

All PWA routes are in `routes/core/pwa.routes.php`:
- `/pwa/sync/*` — sync endpoints
- `/pwa/diagnostics/*` — diagnostic endpoints
- `/pwa/staging/*` — staging endpoints

---

## Node vs PWA Separation Rule

**Never mix Node and PWA domains.**

| Node (`tz_node_*`) | PWA (`tz_pwa_*`) |
|-------------------|-----------------|
| Device mesh sync | Offline queue |
| Peer routing | Service worker |
| Edge compute | IndexedDB |
| Device identity | Client persistence |
| Federated git | Manifest logic |

---

## Source Reference

`CodeToUse/PWA/platform/` — platform layer source
