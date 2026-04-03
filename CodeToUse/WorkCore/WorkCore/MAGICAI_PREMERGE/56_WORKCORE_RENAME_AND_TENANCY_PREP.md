# WorkCore Structural Rename + Company Tenant Prep

## Added
- `magicai_overlay/app/Providers/WorkCoreServiceProvider.php`
- `magicai_overlay/app/Support/workcore_helpers.php`
- `magicai_overlay/app/Support/tenant_helpers.php`
- `magicai_overlay/app/Traits/BelongsToCompany.php`
- `magicai_overlay/config/workcore.php`
- `magicai_overlay/routes/workcore.php`
- `MAGICAI_PREMERGE/company_id tenancy conversion task.md`

## Compatibility kept
- `WorkCoreServiceProvider` now bridges to `WorkCoreServiceProvider`
- `config/workcore.php` now aliases `config/workcore.php`
- `routes/workcore.php` now aliases `routes/workcore.php`
- `worksuite_label()` now proxies to `workcore_label()`

## Notes
This pass does **not** fully refactor all tenancy logic in the full app. It adds the
starter files and prompt needed for the full `company_id` boundary conversion while
preserving `team_id` as the crew layer.
