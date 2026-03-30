# Workflow Module (Worksuite) — v4.1.0

Stabilization pass: named routes, permissions, plan wiring, settings UI, and verification page.

## Key Routes
- `workflow.index` — main list
- `workflow.check` — wiring check (requires `view_workflow`)
- `workflow.settings` — settings UI (requires `manage_workflow`)

## Install
1. Upload ZIP via **Settings → Modules → Install** (or copy `Workflow/` to `Modules/`).
2. Run:
   ```bash
   php artisan optimize:clear
   php artisan view:clear
   composer dump-autoload -o
   php artisan migrate
   ```
3. Assign plans & roles as needed.

## Verify
- Visit `/workflow/check` as Admin — you should see the Wiring Check page.
- Sidebar shows only when plan includes `workflow` and user has `view_workflow`.
