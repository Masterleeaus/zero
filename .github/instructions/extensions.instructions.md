---
applyTo: "CodeToUse/**,app/Extensions/**"
---

# Extension Merge Instructions

- Code in `CodeToUse/` is source material, not automatically production-ready host code.
- Fully scan source extensions before moving or rewriting anything.
- Preserve original logic first.
- Extract feature-specific logic and discard duplicated app infrastructure.
- Do not blindly mount source extensions unchanged if the task is to merge into core.
- Keep:
  - controllers
  - services
  - jobs
  - listeners
  - views
  - feature config
  - wizard steps
  - automation logic
  - agent logic
- Discard or adapt:
  - duplicate auth
  - duplicate providers
  - duplicate global config
  - duplicate middleware
  - duplicate host-owned domain entities
- If DB tables already exist, avoid duplicate create-table migrations.
- Document source-to-host mapping for every major moved file or subsystem.
