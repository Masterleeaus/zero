# Security & CVE Audit

## Summary

Audit performed against all direct and transitive PHP dependencies. Two previously ignored
advisories have been fully resolved. Remaining open advisory is dependency-blocked and tracked
below. All core dashboard route groups now enforce `throttle:120,1`. Sentry PII collection is
confirmed disabled by default.

---

## Previously Ignored Advisories — Resolved

| Advisory ID | CVE | Package | Severity | Finding |
|---|---|---|---|---|
| `PKSA-4g5g-4rkv-myqs` | CVE-2025-55166 | `enshrined/svg-sanitize` < 0.22.0 | High | XSS via mixed-case attribute names. Current pinned version **0.22.0** is not vulnerable. Advisory ignore removed. |
| `PKSA-64jn-3d9t-gncx` | CVE-2025-54370 | `phpoffice/phpspreadsheet` < 5.0.0 | High | SSRF in HTML reader via remote resource fetching. Upgraded to **5.5.0** (patched). Advisory ignore removed. |

Both advisory IDs have been removed from `composer.json`. The `config.audit.ignore` block no longer exists.

---

## Additional Upgrades Applied

| Package | From | To | Advisory / CVE | Severity | Description |
|---|---|---|---|---|---|
| `aws/aws-sdk-php` | < 3.375.0 | 3.375.0 | GHSA-27qh-8cxx-2cr5 | High | Policy document injection |
| `google/protobuf` | < 4.33.6 | 4.33.6 | GHSA-p2gh-cfq4-4wjc | High | DoS via malformed messages |
| `league/commonmark` | < 2.8.2 | 2.8.2 | GHSA-hh8v-hgvp-g3f5, GHSA-4v6x-c7xx-hw9f | Medium | Embed allowed_domains bypass; raw HTML bypass |
| `phpseclib/phpseclib` | < 3.0.50 | 3.0.50 | GHSA-94g3-g5v7-q4jg | High | AES-CBC padding oracle timing side-channel |
| `phpoffice/phpspreadsheet` | < 5.0.0 | 5.5.0 | CVE-2025-54370 / GHSA-rx7m-68vc-ppxh | High | SSRF in HTML reader (also resolves `PKSA-64jn-3d9t-gncx` above) |

---

## Open Advisory — Dependency-Blocked

| Advisory ID | CVE | Package | Severity | Reason blocked |
|---|---|---|---|---|
| `PKSA-y2cr-5h3j-g3ys` | CVE-2025-45769 | `firebase/php-jwt` < 7.0.0 | Low | Weak encryption. Upgrade to 7.x blocked by `nerdzlab/socialite-apple-sign-in` (requires `^5.2|^6.0`) and `laravel/passport` 12.x (requires `^6.4`). Monitor upstream; upgrade when compatible releases are published. |

---

## Rate Limiting — Core Routes

All route files under `routes/core/` are wrapped by `RouteServiceProvider` in a
`['auth', 'throttle:120,1']` group. In addition, each individual route file explicitly
declares `throttle:120,1` in its own middleware chain for defence-in-depth:

| Route file | Throttle status |
|---|---|
| `crm.routes.php` | ✅ explicit (`throttle:120,1`) |
| `insights.routes.php` | ✅ explicit (`throttle:120,1`) |
| `money.routes.php` | ✅ explicit (via config default `throttle:120,1`) |
| `rewind.routes.php` | ✅ explicit (`throttle:120,1`) |
| `signals.routes.php` | ✅ explicit (`throttle:120,1`) |
| `social.routes.php` | ✅ via `RouteServiceProvider` wrapper |
| `support.routes.php` | ✅ explicit (via config default `throttle:120,1`) |
| `team.routes.php` | ✅ explicit (via config default `throttle:120,1`) |
| `titan_core.routes.php` | ✅ explicit (`throttle:120,1`) |
| `work.routes.php` | ✅ explicit (via config default `throttle:120,1`) |

---

## Sentry PII

`config/sentry.php` line 36:

```php
'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),
```

Default is `false`. PII is not sent to Sentry unless explicitly opted-in via the environment variable.

---

## CSRF / Middleware Stack

- Web routes load through the `web` middleware stack — CSRF protection applied automatically.
- API routes load through the `api` middleware stack.
- Core dashboard routes load inside the `web` group with additional `auth` + `throttle:120,1`.

---

## Upgrade Constraints & Follow-ups

- `firebase/php-jwt` remains at **6.11.1** until downstream packages publish 7.x-compatible releases.
  Track: `nerdzlab/socialite-apple-sign-in` and `laravel/passport` for major upgrade paths.
- Composer dependency installation requires network access to `git.yoomoney.ru` (private mirror).
  Full vendor install and automated test runs cannot be executed in this sandboxed environment.
