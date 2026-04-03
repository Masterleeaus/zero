# Install & Smoke Test

1) Enable + migrate
```bash
php artisan module:enable FacilityManagement || true
php artisan migrate
```
2) Open:
- `/facility` (dashboard)
- `/sites`, `/buildings`, `/units`, `/assets`, `/inspections`, `/docs`, `/meters`, `/reads`, `/occupancy`

3) AI checks (optional keys in .env):
- POST `/units/{id}/ai-checklist`
- POST `/assets/{id}/ai-pm`
- POST `/docs/{id}/ai-summary`


## CSV Import Quickstart
- Visit `/facility/import` and upload a CSV for sites/buildings/unit_types/units/assets.
- Rows are upserted; reruns are safe.
