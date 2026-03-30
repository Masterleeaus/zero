# Uninstall

- Disable the module:
```bash
php artisan module:disable FacilityManagement || true
```
- Drop tables only if you are certain:
```bash
php artisan migrate:rollback --step=6
```
- Remove menu/permissions wiring in your host app if added.
