# WorkCore Route Extension

This archive is prepared for MagicAI integration.

## Added

- `routes/workcore/workcore.php`

## Expected host behavior

MagicAI `routes/web.php` should auto-load all PHP files from:

- `routes/workcore/`

## Dev workflow

Add route files such as:

- `routes/workcore/customers.php`
- `routes/workcore/sites.php`
- `routes/workcore/service_jobs.php`
- `routes/workcore/checklists.php`
- `routes/workcore/team.php`
- `routes/workcore/money.php`

## Notes

- Keep MagicAI host `routes/web.php` authoritative.
- Keep WorkCore routes modular.
- Preserve AU cleaning vocabulary at the surface layer.
- Preserve `company_id` tenancy and `team_id` as internal grouping only.
