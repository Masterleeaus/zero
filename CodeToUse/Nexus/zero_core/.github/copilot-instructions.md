# TitanZero Repository Instructions

TitanZero is an AI-first service business operating system being built by merging reusable systems into an existing Laravel host core.

## Core rules

- Always scan the latest host/base code first before making changes.
- Treat the current repository as the canonical working system.
- Preserve existing code. Prefer reuse, extension, adaptation, and refactor over replacement.
- Never scaffold a new subsystem when an existing one can be integrated or extended.
- Do not infer architecture from names like core, engine, module, or extension. Inspect files.
- For merge work, use original source logic first, then do renaming/restructure in later passes.
- Do not create duplicate CRM, Money, or Work domain stacks. Bridge to existing host domains where possible.
- Do not overwrite host auth, tenancy, middleware, provider, queue, mail, config, or shared infrastructure without strong justification.
- Database tables may already exist. Check actual schema usage before adding migrations.
- For shared entities like customers, jobs, invoices, payments, contacts, companies, teams, and users, bridge to host systems instead of preserving duplicate silos.

## Host conventions

- Respect existing route loading conventions, including modular route files if present.
- Respect themed view resolution and existing Blade structure.
- Keep named routes stable where possible.
- Avoid duplicate provider registration and duplicate route definitions.
- Keep menu wiring aligned to real named routes.

## Merge doctrine

- Source systems should be scanned fully before extraction.
- Strip duplicated infrastructure from imported code.
- Keep feature-specific logic:
  - domain controllers
  - services
  - jobs
  - listeners
  - wizard flows
  - automation logic
  - dashboards
  - analytics
  - chat and agent logic
- Defer semantic renames unless the task explicitly requests a rename pass.

## Validation

Before finishing:
- ensure app boots
- ensure routes resolve
- ensure providers load
- ensure themed views render
- ensure no duplicate class/route/provider conflicts were introduced
- ensure existing CRM/Money/Work systems still function structurally

Trust these instructions first. Search only when these instructions are incomplete or contradicted by the codebase.
