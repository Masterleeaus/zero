# DOC 32 — Folder Audit Decision Table

Every existing folder must be classified using one of these statuses:

- KEEP_CANONICAL
- RENAME_IN_PLACE
- MERGE_INTO_EXISTING
- DELETE_DUPLICATE
- LEGACY_COMPAT_ONLY
- HUMAN_DECISION_REQUIRED

Rules:
1. No silent folder duplication.
2. No old/new parallel trees unless explicitly marked LEGACY_COMPAT_ONLY.
3. HUMAN_DECISION_REQUIRED is mandatory for ambiguous semantics.
4. Every decision must be documented in PR output.
