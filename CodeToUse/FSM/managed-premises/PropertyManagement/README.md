# PropertyManagement Module (Titan)

A tenant (account) module that collates **property profiles** and **jobs/tasks at a property**.

Designed for:
- **Tradies** (site access notes, hazards, keys/lockbox info, quick job history)
- **Real estate agents** (properties, tenants/owners/maintenance contacts, scheduled services)
- **Cleaners** (service windows, access notes, recurring job tracking)

## Features
- Properties CRUD (address, access notes, hazards, keys)
- Contacts per property (agent/owner/tenant/cleaner/emergency/tradie)
- Units per property (for buildings / strata)
- Property jobs/tasks log (lightweight; can link to existing records)
- Safe **Titan Zero** UI hook via `@includeIf('titanzero::partials.ask-titan', ...)` (no direct model calls)

## Install
1) Upload module folder to `Modules/PropertyManagement`
2) Enable module in Worksuite
3) Run migrations

```bash
php artisan module:migrate PropertyManagement
```

## Notes
- Routes are tenant-scoped and use `account/property-management/*`.
- Permissions are checked in controllers (`view_property`, `add_property`, `edit_property`, `delete_property`).
