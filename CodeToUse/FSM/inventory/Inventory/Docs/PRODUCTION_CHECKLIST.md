# Inventory Module — Production Checklist

## Environment & Config
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`.
- [ ] Sanctum/Auth guard aligned; confirm `auth.defaults.guard` matches your login.
- [ ] Multi-tenant? Set `INVENTORY_SIDEBAR_INCLUDE=true` if you want the sidebar partial.
- [ ] Menu table name via `INVENTORY_MENU_TABLE` if not `menus`.

## Dependencies
- [ ] `spatie/laravel-permission` installed & migrated (optional but recommended).
- [ ] Ensure queue driver is set (for mail/notifications if you add them later).
- [ ] Cache driver configured (redis/memcached preferred).

## Database
- [ ] Run **all** module migrations (includes soft deletes, FKs & indexes).
- [ ] Review generated migrations vs your schema; add/adjust FKs for custom tables.
- [ ] Seed roles/permissions in **non-prod** with `InventoryRolesSeeder`, otherwise only `InventoryPermissionsSeeder`.

## Security & Access
- [ ] Map roles: `admin`, `inventory-manager`, `inventory-viewer` to real users.
- [ ] Verify policies: `inventory.view` for read paths, `inventory.manage` for mutations.
- [ ] Ensure Sanctum tokens/SPA are in place for API clients.

## UI & UX
- [ ] Hook `_sidebar.blade.php` into your layout if you want a permanent menu.
- [ ] Verify sticky header does not clash with app CSS.
- [ ] Test confirm prompts on delete/restore and bulk actions.

## API
- [ ] Review `Docs/openapi.yaml` and publish to your API portal (Stoplight, Swagger UI).
- [ ] Use `Docs/client-ts-typed.ts` or `Docs/client-php-typed.php` as starting SDKs.
- [ ] Confirm `X-Tenant-ID` header behaviour in multi-tenant scenarios.

## Data Flows
- [ ] Stocktake finalize now **reconciles** by posting adjust movements to match counted quantities.
- [ ] Validate movement permissions & audit trail entries land in `inventory_audits`.

## Observability
- [ ] Add log channel for inventory events if you need deeper traces.
- [ ] Wire metrics (e.g., movements/day, negative on-hand alerts) later as needed.

## Rollback Plan
- [ ] Back up DB before first deploy.
- [ ] You can roll back module migrations with `php artisan migrate:rollback` (scope by path if you separate migrations).
- [ ] Soft deletes allow restoring records; audit log provides change history.


### Reconciliation safety & notifications
- [ ] Set `INVENTORY_RECON_REQUIRE_PREVIEW=true` to force preview before finalize (default false).
- [ ] Optionally tune `INVENTORY_RECON_PREVIEW_TTL=15` (minutes).
- [ ] To notify on finalize, set:
  - `INVENTORY_NOTIF_ENABLED=true`
  - `INVENTORY_NOTIF_EMAIL_TO=ops@example.com,team@example.com`
  - `INVENTORY_NOTIF_WEBHOOK=https://hooks.example.com/inventory`
