# Timesheet Module — Pass 5 (Final)

Build date: 2026-01-02 (Australia/Melbourne)

## What this module provides
- Timesheet entry CRUD (tenant-safe)
- Core Tasks integration (adapter-based)
- Core HR rate integration (adapter-based)
- Work Order linking (adapter-based)
- Timer (clock on/off) -> auto-create timesheet entry
- Weekly submission + approvals (approve/reject)
- Reporting UI + API reporting endpoints
- Export (permission-gated)

## Worksuite/Titan compliance highlights
- Tenant routing is under `account/timesheets/*`
- All endpoints are auth + permission gated
- No AI/provider calls (Titan Zero law respected)
- Optional integrations are guarded (no fatal deps if modules absent)
- Language keys used for menu + UI labels (tradie-ready)

## Install / Upgrade (server)
```bash
cd /home/saassmar/domains/ops.tradiesm.art/public_html
php artisan module:migrate Timesheet
php artisan module:seed Timesheet
php artisan optimize:clear
```

## Configuration
See `Config/config.php` for core table/column mappings used by:
- Core tasks provider
- Core HR provider
- Core work order provider
