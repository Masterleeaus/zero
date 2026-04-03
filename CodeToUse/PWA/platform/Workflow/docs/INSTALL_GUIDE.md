# Installation Guide — Workflow Module

## Requirements
- Worksuite SaaS (matching your core version)
- PHP 8.1+

## Steps
1. Upload module zip in Super Admin: **Settings → Modules → Install**.
2. Clear caches and autoload, then run migrations:
   ```bash
   php artisan optimize:clear
   php artisan view:clear
   composer dump-autoload -o
   php artisan migrate
   ```
3. Ensure your target packages include `workflow` (migration does this for common Pro/Enterprise names).
4. Confirm Admin role holds `view_workflow` / `manage_workflow`.

## Troubleshooting
- If the sidebar shows but link 404s: run `php artisan route:list | grep workflow`.
- If route not found: ensure providers load and caches are cleared.
