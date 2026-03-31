---
applyTo: "app/Models/**/*.php,app/**/Models/**/*.php,database/migrations/**/*.php"
---

# Model and Schema Instructions

- Host models remain canonical for shared business entities.
- Do not introduce duplicate customer, invoice, job, payment, user, team, or company models if host equivalents already exist.
- Bridge source logic to host models where possible.
- Only preserve unique source models when they represent truly distinct automation, wizard, analytics, or agent data.
- Database tables may already exist. Inspect before adding migrations.
- Prefer additive or bridge logic over destructive schema changes.
- Keep tenancy consistent with the host implementation.
