# FacilityManagement (nWidart)

Facilities module with Sites, Buildings, Units, Unit Types, Assets, Inspections, Documents, Meters, Meter Reads, and Occupancy — with AI helpers.

## Install
```bash
php artisan module:enable FacilityManagement || true
php artisan migrate
php artisan route:list | grep facility
```

## AI
Set `.env` keys if you want live AI:
```
AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
```
Without keys, deterministic drafts are returned.

## Notes
- Views are lightweight; plug in your models.
- Migrations are idempotent (guarded by `hasTable`).
- Menu + permissions live in `Config/`.


## Models, Policies, and Seeder
This build ships with:
- Eloquent models in `Entities/` with relationships.
- Policies registered via `Providers/AuthServiceProvider.php`.
- `Database/Seeders/FacilitySeeder.php` that inserts permissions into `permissions` table (if present) and grants them to the `admin` role (if present).

### Run seeder
```bash
php artisan db:seed --class="Modules\FacilityManagement\Database\Seeders\FacilitySeeder"
```


## Tenancy
- Migration adds `tenant_id` to facility tables if they exist.
- Middleware + model trait auto-scope queries when `X-Tenant-ID` header is present and tables include `tenant_id`.

## CSV Import
- UI: `/facility/import`
- Entities: sites, buildings, unit_types, units, assets
- Upserts by `code` (or `label` for assets)


## Exporters
- CSV export endpoint: `GET /facility/export/{entity}` for any of: sites, buildings, unit_types, units, assets, inspections, docs, meters, reads, occupancy.

## Work Order Bridge
- Buttons appear next to assets and inspections: **Create Work Order**.
- If the JobManagement module is present (with `work.create` route), we redirect there with prefilled parameters.
- If not present, you get a JSON notice.

## Notifications
- Command: `php artisan facility:notify`
- Config keys (in `Config/config.php`):
  - `facility.notify.doc_expiry_days` (default: 30)
  - `facility.notify.inspection_overdue_hours` (default: 24)
  - `facility.notify.notify_user_id` (default: first user)
- Add to scheduler: `$schedule->command('facility:notify')->hourly();`


## Dashboard Widgets
- Live counts for sites, buildings, units, assets, upcoming inspections (7 days), expiring docs (30 days), occupied and vacant units.
- Embedded **Energy Trend** sparkline (SVG) and **CSV export** for the last N months, filterable by `meter_type`.

### Energy Trend routes
```
GET /facility/energy/trend.csv?months=12&meter_type=power
GET /facility/energy/trend.svg?months=12&meter_type=power
```
Meter types: `power`, `water`, `gas`, or `*` for all.


## Reports
- **Building energy** (monthly totals) → `/facility/reports/building-energy.csv?months=12&meter_type=power`
- **Inspection SLA** (avg hours, overdue rate) → `/facility/reports/inspection-sla.csv`
- **Building occupancy** (rate) → `/facility/reports/building-occupancy.csv`
- Reports page: `/facility/reports` (links to the CSVs)


## Building Dashboards & Unit Timelines
- Per-building dashboard: `/buildings/{id}/dashboard` — units, assets, occupancy, inspections, expiring docs, and a 12‑month energy sparkline.
- Unit timeline: `/units/{id}/timeline` — chronological feed of inspections, docs, meter reads, and occupancy changes.

## RBAC on Reports
- The reports page `/facility/reports` is protected by `facilities.view`. Unauthorized users receive a 403.


## Role Matrix (Seeder)
- Presets: `facility_viewer`, `facility_manager`, `facility_admin`
- Viewer → `*.view` only
- Manager → viewer perms + create/edit/delete/schedule/complete/etc.
- Admin → all facility permissions
Run:
```bash
php artisan db:seed --class="Modules\FacilityManagement\Database\Seeders\FacilityRoleMatrixSeeder"
```

## Per-Building Energy Breakdown
- Route: `/buildings/{id}/energy` — table with monthly totals per meter type (power/water/gas).

## Compliance Pack (PDF/HTML)
- Route: `/buildings/{id}/compliance-pack`
- If `barryvdh/laravel-dompdf` is installed, it downloads a PDF. Otherwise it returns HTML you can print to PDF.
