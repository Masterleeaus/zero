# WorkCore Phase 2 — Dependency Resolution Report

Generated: 2026-04-03  
Source audit: `WorkCore.zip → MAGICAI_PREMERGE/06_DEPENDENCY_AUDIT.md`

---

## Config & Helper File Placements

| File | Status | Notes |
|------|--------|-------|
| `config/workcore.php` | ✅ Placed | Vertical setting, labels map, feature flags |
| `config/verticals.php` | ✅ Placed | Multi-vertical vocabulary (cleaning / facilities / maintenance) |
| `app/Services/VerticalLanguageResolver.php` | ✅ Placed | Resolves per-vertical labels and feature flags |
| `app/Support/workcore_helpers.php` | ✅ Placed | Global `workcore_label()`, `workcore_feature()`, `workcore_vertical()` helpers |
| `composer.json` autoload `files` | ✅ Registered | `app/Support/workcore_helpers.php` added to autoload files |

---

## WorkCore-Specific Package Audit

Packages listed in `06_DEPENDENCY_AUDIT.md` were compared against the host `composer.json`.

### Already Present in Host

| Package | Reason |
|---------|--------|
| `barryvdh/laravel-dompdf` | In host `require` |
| `doctrine/dbal` | In host `require` |
| `guzzlehttp/guzzle` | In host `require` |
| `google/apiclient` | In host `require` (as `google/apiclient`) |
| `intervention/image` | In host `require` |
| `laravel/cashier` | In host `require` |
| `laravel/framework` | In host `require` (host-owned) |
| `laravel/sanctum` | In host `require` (host-owned) |
| `laravel/socialite` | In host `require` |
| `laravel/tinker` | In host `require` |
| `league/flysystem-aws-s3-v3` | In host `require` |
| `pusher/pusher-php-server` | In host `require` |
| `razorpay/razorpay` | In host `require` |
| `sentry/sentry-laravel` | In host `require` |
| `twilio/sdk` | In host `require` |
| `yajra/laravel-datatables-oracle` | In host `require` |

### Skipped — Host-Owned / Generic Laravel Infrastructure

| Package | Reason |
|---------|--------|
| `laravel/fortify` | Host uses Passport/Sanctum stack; Fortify not needed |
| `laravel/helpers` | Host already includes Laravel 10 helpers globally |
| `laravel/slack-notification-channel` | Not a WorkCore feature; omit unless specifically needed |
| `laravel/vonage-notification-channel` | Host has Twilio; Vonage not required |
| `nwidart/laravel-modules` | Host uses a custom extensions/providers pattern, not nwidart modules |
| `laravel-lang/lang` | Host uses `mcamara/laravel-localization`; different i18n stack |
| `froiden/laravel-installer` | Installer package; not a WorkCore domain feature |
| `froiden/envato` | License validation; not a WorkCore domain feature |
| `pcinaglia/laraupdater` | Updater; host has `magicai/magicai-updater` |
| `maatwebsite/excel` | Not required by WorkCore domain features merged so far |
| `opcodesio/log-viewer` | Dev/admin tooling; not a WorkCore domain dependency |
| `spatie/laravel-backup` | Infrastructure; not a WorkCore domain feature |
| `spatie/laravel-model-status` | Not used in WorkCore domain models merged so far |

### Deferred — Evaluate at Feature Merge Time

| Package | Associated Feature | Notes |
|---------|--------------------|-------|
| `webklex/laravel-imap` | Email ingestion / Support | Add when support email feature is enabled |
| `eluceo/ical` | Calendar / Schedule & Dispatch | Add when iCal export is wired |
| `endroid/qr-code` | QR codes on jobs/invoices | Add when QR feature is enabled |
| `pusher/pusher-push-notifications` | Team Chat push | Deferred with `teamchat` feature flag |
| `quickbooks/v3-php-sdk` | Accounting integration | Add when QuickBooks integration is merged |
| `stripe/stripe-php` | Payments | Host uses Cashier; evaluate overlap |
| `mollie/laravel-mollie` | Payments | Add when Mollie gateway is enabled |
| `paypal/rest-api-sdk-php` | Payments | Add when PayPal gateway is enabled |
| `authorizenet/authorizenet` | Payments | Add when Authorize.net gateway is enabled |
| `kingflamez/laravelrave` | Payments (Flutterwave) | Add when Flutterwave gateway is enabled |
| `unicodeveloper/laravel-paystack` | Payments (Paystack) | Add when Paystack gateway is enabled |
| `ivanomatteo/laravel-device-tracking` | Security / Devices | Evaluate when device auth is needed |

---

## Verification

```bash
php artisan tinker --execute="config('workcore.vertical');"
# Expected: 'cleaning'

php artisan tinker --execute="workcore_label('sites');"
# Expected: 'Jobs'

php artisan tinker --execute="workcore_feature('teamchat');"
# Expected: false (deferred)
```

---

## Notes

- `app/Helpers/helpers.php` defines the canonical `workcore_label()` and `workcore_feature()` functions that read from `config('workcore.labels')` and `config('workcore.features')` respectively.
- `app/Support/workcore_helpers.php` provides the same API with `function_exists` guards — it defers to the `helpers.php` versions and adds `workcore_vertical()`.
- `app/Services/VerticalLanguageResolver.php` provides an injectable class for controllers/services that need vertical vocabulary resolution.
- `config/verticals.php` holds full multi-vertical vocabulary maps for advanced use (e.g., switching the entire UI vocabulary for facilities or maintenance verticals).
