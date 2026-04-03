# Merge Playbook

**Agent:** Copilot  
**Purpose:** Step-by-step guide for merging CodeToUse source modules into the Titan Zero host application

---

## Merge Order (Required Sequence)

1. Scan source module against host (never assume structure)
2. Map source tables → host tables (check for existing migrations)
3. Map source models → host models (check App\Models\)
4. Identify feature-specific logic to keep
5. Identify duplicated infrastructure to discard
6. Integrate models, services, controllers
7. Add routes to routes/core/
8. Add views to resources/views/default/panel/
9. Wire service provider or register in existing provider
10. Run migrations (additive only — no destructive changes)
11. Write tests
12. Generate pass report in docs/

---

## Discard List (Infrastructure Duplicates)

When merging any source module, always discard:
- `auth` scaffolding (use host auth stack)
- `roles` / `permissions` (use host Spatie/role system)
- `users` table migrations (already exists in host)
- `companies` / `teams` table migrations (already exists)
- Global middleware registrations (use host middleware)
- Generic providers (use host AppServiceProvider)
- Queue/mail/cache config (use host config/)
- Asset pipelines (use host vite.config.mjs)

---

## Keep List (Feature Logic)

Always keep:
- Controllers (adapt namespace and middleware)
- Services (adapt to host service container)
- Jobs / Listeners
- Models for new entities
- Feature-specific views
- Wizard steps / automation logic
- Agent logic
- API endpoints unique to the feature

---

## Required Output Per Merge

Each merge pass must produce a report in `docs/` named:
`{FEATURE}_PASS_IMPLEMENTATION_REPORT.md`

Containing:
- Files created
- Tables migrated
- Routes registered
- Services registered
- Tests written
- Conflicts deferred
