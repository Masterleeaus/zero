---
applyTo: "app/**/*.php,config/**/*.php,bootstrap/**/*.php"
---

# Core Merge Instructions

- This repository is an evolving merged core, not a fresh Laravel app.
- Never replace host infrastructure casually.
- Preserve:
  - existing providers
  - config ownership
  - auth stack
  - tenancy logic
  - queue/mail/cache ownership
- Merge source feature logic into host core deliberately and incrementally.
- Reuse existing services and systems before introducing new ones.
- Document conflicts and deferred rename work instead of hiding uncertainty in code.
