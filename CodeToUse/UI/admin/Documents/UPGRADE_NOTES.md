# Documents Module — Upgrade Notes (Pass 1–5)

This zip contains the Documents module stabilized for Worksuite/Nwidart.

## What was fixed

### Pass 1 — Boot-safe (parse/autoload)
- Removed corrupted namespaces (`namespace ModulesDocuments...;`)
- Fixed `Providers/RouteServiceProvider.php` fatal namespace duplication
- Removed duplicate `routes/` folder; canonical routes live in `Routes/web.php`
- Config files now return arrays (no namespaces)

### Pass 2 — Sidebar stability
- Sidebar partial hardened to avoid fatal errors during sidebar build
- Restored expected menu structure & child links

### Pass 3 — Feature stabilization
- Tenant resolution made safe (no hard dependency on `company()` helper)
- Fixed invalid PHP in file upload path string

### Pass 4 — Letters quarantined
- Letters subsystem removed to prevent dead-code runtime errors

### Pass 5 — Packaging & cleanup
- Removed backup/temporary route files
- Added this upgrade note

## Install / Upgrade

```bash
cd /home/saassmar/domains/ops.tradiesm.art/public_html
php artisan optimize:clear
composer dump-autoload
php artisan migrate
```

## Verify

```bash
cd /home/saassmar/domains/ops.tradiesm.art/public_html
php artisan route:list | grep documents
```

Then visit:
- `/documents/manager/dashboard`
- `/documents/general`
- `/documents/swms`
- `/documents/templates`
- `/documents/folders`
- `/documents/files`

## Notes
- Permissions: migration registers `manage_documents` in Worksuite-style tables when present.
- Routes are currently `auth` protected; permission gating can be enforced in controllers/menu as needed.
