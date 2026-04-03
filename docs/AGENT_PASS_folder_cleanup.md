# AGENT PASS LOG — Folder Cleanup + Domain Sorting

**Agent:** Copilot  
**Pass:** folder-cleanup-domain-sorting  
**Date:** 2026-04-03  
**Issue:** Folder Cleanup + Domain Sorting (Post-Scan Required)

---

## Files Created

| File | Purpose |
|------|---------|
| `docs/SCAN_REPORT_repo_cleanup.md` | Repository deep-scan report |
| `docs/DUPLICATE_CODE_MAP.md` | Duplicate detection map |
| `docs/DOMAIN_CLASSIFICATION_MAP.md` | Feature → domain → folder classification |
| `docs/DOC_INDEX.md` | Full documentation index |
| `.github/agent-docs/copilot-instructions.md` | Mirrored copilot instructions |
| `.github/agent-docs/scan-rules.md` | Mirrored scan rules |
| `.github/agent-docs/domain-classification-map.md` | Mirrored domain map |
| `.github/agent-docs/duplicate-code-map.md` | Mirrored duplicate map |
| `.github/agent-docs/route-map.md` | Mirrored route manifest |
| `.github/agent-docs/schema-map.md` | Mirrored schema map |
| `.github/agent-docs/merge-playbook.md` | Merge playbook (new) |
| `.github/agent-docs/pwa-build-plan.md` | PWA build plan (new) |
| `.github/agent-docs/pwa-architecture.pdf` | Mirrored PWA architecture |
| `.github/agent-docs/merge-validation.md` | Mirrored merge validation |
| `.github/agent-docs/provider-binding-map.md` | Mirrored provider map |

---

## Domain Folders Created

`CodeToUse/AI/`, `CodeToUse/Comms/`, `CodeToUse/CRM/`, `CodeToUse/Dispatch/`,
`CodeToUse/Finance/`, `CodeToUse/FSM/`, `CodeToUse/Jobs/`, `CodeToUse/Lifecycle/`,
`CodeToUse/Mobile/`, `CodeToUse/Node/`, `CodeToUse/Omni/`, `CodeToUse/PWA/`,
`CodeToUse/Routing/`, `CodeToUse/Scheduling/`, `CodeToUse/Signals/`, `CodeToUse/Tenancy/`,
`CodeToUse/UI/`, `CodeToUse/Utilities/`, `CodeToUse/Voice/`, `CodeToUse/WorkCore/`,
`CodeToUse/Extensions/`, `CodeToUse/Nexus/`

---

## Files Moved (Existing Folders → Domain)

| Source | Destination |
|--------|-------------|
| `CodeToUse/aicore/` | `CodeToUse/AI/aicore/` |
| `CodeToUse/AiSocialMedia/` | `CodeToUse/AI/AiSocialMedia/` |
| `CodeToUse/SocialMediaAgent/` | `CodeToUse/AI/SocialMediaAgent/` |
| `CodeToUse/SocialMedia/` | `CodeToUse/Comms/SocialMedia/` |
| `CodeToUse/leads/` | `CodeToUse/CRM/leads/` |
| `CodeToUse/feedback/` | `CodeToUse/CRM/feedback/` |
| `CodeToUse/compliance-auditing/` | `CodeToUse/Tenancy/compliance-auditing/` |
| `CodeToUse/managed-premises/` | `CodeToUse/FSM/managed-premises/` |
| `CodeToUse/Odoo/` | `CodeToUse/FSM/Odoo/` |
| `CodeToUse/work/` | `CodeToUse/WorkCore/work/` |
| `CodeToUse/mobile_app_backend/` | `CodeToUse/WorkCore/mobile_app_backend/` |
| `CodeToUse/utilities/` | `CodeToUse/Utilities/utilities/` |

---

## Mobile Apps Copied

| Source | Destination |
|--------|-------------|
| `mobile_apps/TitanCommand/` | `CodeToUse/Mobile/TitanCommand/` |
| `mobile_apps/TitanGo/` | `CodeToUse/Mobile/TitanGo/` |
| `mobile_apps/TitanPortal/` | `CodeToUse/Mobile/TitanPortal/` |
| `mobile_apps/TitanMoney/` | `CodeToUse/Mobile/TitanMoney/` |
| `mobile_apps/TitanPro/` | `CodeToUse/Mobile/TitanPro/` |

---

## ZIP Files Extracted and Deleted

30 ZIP files processed across `CodeToUse/`, root, and `docs/`.
All extracted to appropriate domain folders.
All `__MACOSX` metadata folders removed.

---

## docs/ Reorganisation

| Action | Files |
|--------|-------|
| Created `docs/finance/` | 6 FINANCE_*.md files |
| Created `docs/routes/` | 4 ROUTE_*.md files |
| Created `docs/fsm/` | 4 FSM_*.md files |
| Created `docs/zero/` | 3 ZERO_*.md files |
| Created `docs/core/` | Core, Schema, View, Workflow docs |
| Moved to `docs/titancore/` | 30 TITAN_*.md files |
| Moved to `docs/merge_reports/` | MERGE_*.md files |

---

## Files Avoided (Not Modified)

- All `app/` production code
- `database/migrations/`
- `routes/`
- `resources/`
- `config/`
- `mobile_apps/` (original preserved, copied to Mobile domain)

---

## Integrations Added

None — this was a folder organisation and documentation pass only.

---

## Conflicts Resolved

- None — no code conflicts. All moves were safe file system operations.

---

## Duplicates Removed

- All `__MACOSX` metadata directories (macOS resource forks)
- All original ZIP archives after successful extraction

---

## Open Risks

| Risk | Notes |
|------|-------|
| `CodeToUse/AI/AiSocialMedia/` is duplicate of old `AiSocialMedia/` zip | Monitor — both refer to same source |
| Older TitanBot passes (3, 6, 11) still present | Can remove after pass13 integration verified |
| TitanVoiceSuite older passes | Can remove after Unified merge verified |
| `mobile_apps/` still present | Original preserved; CodeToUse/Mobile/ is the working copy |

---

## Next Targets

1. Merge TitanOmni Pass26 HARDENED into Omni channel adapter
2. Merge TitanVoiceSuite Unified into Voice domain
3. Wire CRM leads/demandium into host CRM
4. Merge Finance Modules into host Finance domain
5. Integrate Extension Library extensions selectively
6. Complete Node domain (tz_node_* schemas)
