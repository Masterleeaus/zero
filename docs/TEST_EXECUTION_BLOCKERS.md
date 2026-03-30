# Test Execution Blockers

This environment cannot run the full test/build stack yet. Identified blockers:

- **Composer install fails**: `symfony/cache` v7.4.5 requires `ext-redis >= 6.1`, but the runner has `ext-redis 5.3.7`. Ignoring platform requirements still fails due to blocked download host `git.yoomoney.ru` (dependency of `yoomoney/http-client-psr`).
- **Vendors missing**: Without a successful `composer install`, autoloaded classes and Laravel framework binaries are unavailable, so application boot + migrations + tests cannot execute.
- **DB-dependent flows**: Migrations have not been executed in this environment because of the missing vendor stack; runtime verification of schema/order awaits a full dependency-backed install.

Next steps to unblock:
- Provide a runner with `ext-redis >= 6.1` or adjust dependency versions to match available extensions.
- Mirror or replace the `git.yoomoney.ru` dependency source (or update the package version) so Composer can resolve without the blocked host.
- Re-run `composer run install:no-redis` (or standard install) once the above are addressed, then execute migrations and the targeted test suite.
