---
applyTo: "routes/**/*.php,app/**/Controllers/**/*.php"
---

# Route and Controller Instructions

- Reuse host route loading conventions.
- Prefer modular route files over dumping everything into a single route file.
- Preserve named routes unless a task explicitly requires renaming.
- Avoid duplicate route names, duplicate prefixes, and duplicate registration.
- Controllers should align with existing host patterns for middleware, dependency injection, and view rendering.
- If a controller comes from a merged source extension, adapt it to host auth, tenancy, and domain services rather than preserving old isolated assumptions.
