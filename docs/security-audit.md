# Security Audit — CVE Findings & Remediations

**Audit Date:** 2026-03-30  
**Scope:** Composer dependency CVEs (PKSA-4g5g-4rkv-myqs, PKSA-64jn-3d9t-gncx) and core route rate-limiting

---

## 1. Vulnerability Summary

### CVE-2025-55166 — `enshrined/svg-sanitize` (PKSA-4g5g-4rkv-myqs)

| Field        | Detail |
|--------------|--------|
| Package      | `enshrined/svg-sanitize` |
| CVE ID       | CVE-2025-55166 |
| PKSA ID      | PKSA-4g5g-4rkv-myqs |
| Severity     | High (XSS) |
| Affected     | < 0.22.0 |
| Fix version  | 0.22.0 |
| Description  | Attribute sanitization bypass via case-insensitive attribute names (e.g. `xlink:hReF`), enabling persistent Cross-Site Scripting (XSS) in applications that render user-supplied SVG files inline. |

**Resolution:** The project already requires `"enshrined/svg-sanitize": "^0.22.0"`, which satisfies the fix version. The advisory suppression entry `PKSA-4g5g-4rkv-myqs` has been **removed** from `composer.json`.

---

### CVE-2025-54370 — `phpoffice/phpspreadsheet` (PKSA-64jn-3d9t-gncx)

| Field        | Detail |
|--------------|--------|
| Package      | `phpoffice/phpspreadsheet` |
| CVE ID       | CVE-2025-54370 |
| PKSA ID      | PKSA-64jn-3d9t-gncx |
| GHSA ID      | GHSA-rx7m-68vc-ppxh |
| Severity     | High (CVSS v4.0: 8.7 / v3.1: 7.5) |
| Affected     | < 5.0.0 (no 4.x patch released) |
| Fix version  | 5.0.0 |
| Description  | Server-Side Request Forgery (SSRF) in the `PhpOffice\PhpSpreadsheet\Worksheet\Drawing::setPath()` method. User-controlled input passed through the HTML reader can embed arbitrary URLs (e.g. `<img src>` attributes), triggering requests to internal network resources. |

**Resolution:** The version constraint has been upgraded from `^4.5` to `^5.0`. No 4.x patch was released by the upstream maintainers; 5.0.0 is the earliest fixed release for users on the 4.x branch. The advisory suppression entry `PKSA-64jn-3d9t-gncx` has been **removed** from `composer.json`.

---

## 2. composer.json `audit.ignore` Removal

Both advisory IDs have been removed from `config.audit.ignore` in `composer.json`. The `audit` block has been deleted entirely so that `composer audit` will now report any future regressions without suppression.

---

## 3. Rate Limiting — Core Route Groups

`throttle:120,1` middleware has been added to the outer `Route::middleware([...])` group in every file under `routes/core/`:

| File | Middleware Added |
|------|-----------------|
| `routes/core/crm.routes.php` | `throttle:120,1` |
| `routes/core/insights.routes.php` | `throttle:120,1` |
| `routes/core/money.routes.php` | `throttle:120,1` |
| `routes/core/support.routes.php` | `throttle:120,1` |
| `routes/core/team.routes.php` | `throttle:120,1` |
| `routes/core/work.routes.php` | `throttle:120,1` |

This limits authenticated dashboard users to **120 requests per minute** on all core routes, mitigating brute-force and scraping risks.

---

## 4. Sentry PII Configuration

`config/sentry.php` already has:

```php
'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),
```

The default is `false`, ensuring that personally identifiable information (IP addresses, cookies, request bodies) is **not** sent to Sentry unless explicitly opted in via the `SENTRY_SEND_DEFAULT_PII` environment variable. No change required.

---

## 5. Security Summary

| # | Finding | Status |
|---|---------|--------|
| 1 | CVE-2025-55166 — XSS in `enshrined/svg-sanitize` | ✅ Already at fixed version (^0.22.0); audit ignore removed |
| 2 | CVE-2025-54370 — SSRF in `phpoffice/phpspreadsheet` | ✅ Upgraded constraint from ^4.5 to ^5.0; audit ignore removed |
| 3 | Missing rate limiting on core routes | ✅ `throttle:120,1` added to all 6 core route files |
| 4 | Sentry PII leakage | ✅ `send_default_pii` defaults to `false` — no change needed |
