# Workflow Module (Worksuite SaaS)

Minimal, working Worksuite module that:
- Registers service providers
- Loads routes, views, translations, migrations
- Injects a sidebar menu (plan- & permission-aware)
- Seeds an idempotent `view_workflow` permission and module registry row

Install:
1. Upload this ZIP via **Super Admin → Settings → Modules → Install Module**.
2. Click **Install** to run migrations.
3. Ensure the package includes the `workflow` alias (Super Admin → Packages).
4. Give roles the **view_workflow** permission.

