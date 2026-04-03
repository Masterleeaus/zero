# TitanTrust — Rename & Consistency Scan (Pass T1)

## What was scanned
- All PHP / Blade / JSON / MD files under the extension.

## Results
- No remaining references to TitanVerify, titanverify, titan-verify, titan_verify.
- ServiceProvider file and class renamed to TitanTrustServiceProvider.
- View namespace updated to titantrust:: and views moved to resources/views/titantrust/.
- Routes updated from /dashboard/user/jobs/verify -> /dashboard/user/jobs/trust.

## Recommended runtime smoke checks
1) `php artisan optimize:clear`
2) `php artisan module:migrate TitanTrust`
3) Load:
   - /dashboard/user/jobs/trust/review
   - /dashboard/user/jobs/trust/{jobId}/timeline

If any 500 errors occur, inspect storage/logs/laravel.log for missing layout paths or auth middleware differences.
