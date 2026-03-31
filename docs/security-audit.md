# Security & CVE Audit

## Composer audit (2026-03-30)
- Ran `composer audit --locked`.
- Ignored advisory list removed from `composer.json`.
- Remaining advisory: `PKSA-y2cr-5h3j-g3ys` (firebase/php-jwt < 7.0.0, CVE-2025-45769 – weak encryption, **low** severity). Upgrade to 7.x is blocked because:
  - `nerdzlab/socialite-apple-sign-in` 2.2.2 requires `firebase/php-jwt` `^5.2|^6.0`.
  - `laravel/passport` 12.x requires `firebase/php-jwt` `^6.4`.
  - No released versions of those packages allow 7.x yet. Monitor upstream and upgrade when compatible.

## Actions taken
- Upgraded vulnerable packages:
  - `aws/aws-sdk-php` → 3.375.0 (addresses GHSA-27qh-8cxx-2cr5, policy document injection, **high**).
  - `google/protobuf` → 4.33.6 (GHSA-p2gh-cfq4-4wjc DoS via malformed messages, **high**).
  - `league/commonmark` → 2.8.2 (GHSA-hh8v-hgvp-g3f5 embed allowed_domains bypass, GHSA-4v6x-c7xx-hw9f raw HTML bypass, **medium**).
  - `phpseclib/phpseclib` → 3.0.50 (GHSA-94g3-g5v7-q4jg AES-CBC padding oracle timing, **high**).
  - `phpoffice/phpspreadsheet` → 5.5.0 (CVE-2025-54370 / GHSA-rx7m-68vc-ppxh SSRF in HTML reader, **high**).
- Research on previously ignored advisories:
  - `PKSA-4g5g-4rkv-myqs` (CVE-2025-55166) affects `enshrined/svg-sanitize` < 0.22.0 (XSS via mixed-case attributes). Current version is 0.22.0, so not vulnerable.
  - `PKSA-64jn-3d9t-gncx` (CVE-2025-54370) affects `phpoffice/phpspreadsheet` < 5.0.0. Upgraded to 5.5.0.
- Added rate limiting for core routes (`auth` + `throttle:120,1`) via `RouteServiceProvider`.
- Verified Sentry PII remains disabled by default (`send_default_pii` uses env default false).
- Web routes continue to load through the `web` middleware stack, so CSRF protection is applied; API routes use the `api` stack.

## Upgrade constraints and follow-ups
- `firebase/php-jwt` remains at 6.11.1 until dependent packages publish 7.x-compatible releases. Track:
  - `nerdzlab/socialite-apple-sign-in` for a release supporting `^7.0`.
  - `laravel/passport` major upgrade path that permits `^7.0`.
- Composer dependency installation still fails against the private `git.yoomoney.ru` mirror (network/auth required), so vendor installation/tests could not be executed in this environment.
