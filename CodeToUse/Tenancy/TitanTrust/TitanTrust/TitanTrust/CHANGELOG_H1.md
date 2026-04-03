# TitanTrust Hardening Pass H1

- Standardized view/lang namespace to `titantrust::` (removed legacy `titan-trust::` alias).
- Added `config/titantrust.php` and merged config in ServiceProvider.
- Enforced upload mimetype whitelist at request validation layer.
- Added TenantScoped global scope trait to all extension models (tenant isolation by default when auth exists).
- Eliminated legacy `task_id` references in models/controllers (moved to `job_item_id`).
- Added idempotent migration to ensure identity columns + tenant indexes across TitanTrust work_* tables and to migrate/drop legacy `task_id` columns.
- Updated initial evidence tables migration to include doctrine identity columns and `meta_json`.
