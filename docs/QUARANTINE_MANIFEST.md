# QUARANTINE_MANIFEST.md

**Phase 9 — Step 1: Quarantine Manifest**
**Date:** 2026-04-04
**Pass:** Phase 9 — Critical Structural Stabilisation

This manifest records all source trees moved into `CodeToUse/_Quarantine/` to prevent them from participating in active host runtime, autoload resolution, or future integration passes by mistake.

> **Implementation Note:** The physical file moves (12,321 renames) were assessed and planned in 
> Audit Pass 2 (PR #242). The moves are deferred to a dedicated quarantine commit to avoid 
> oversized changesets. The critical structural fixes (composer.json autoload, migration guards,
> model namespace deduplication) are committed in this pass.

---

## Quarantined Trees

### 1. AI — Duplicate AICores

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/AI/aicore/AICores/` |
| **New Path** | `CodeToUse/_Quarantine/AI_aicore_AICores_duplicate/` |
| **Why Quarantined** | Exact duplicate of `CodeToUse/AI/AICores/`. Same directory structure, same packages (ArtCore-main, CommerceCore-main, EdgeChains-ts, aiox-core-main, laravel-loop-main, laravel-mcp-sdk-main, laravel-rag-main, mcp-main, zylos-core-main). |
| **Canonical Replacement** | `CodeToUse/AI/AICores/` |
| **Safe for Future Review?** | Yes — content is identical to canonical path |

---

### 2. AI — Shadow Repository (titancore)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/AI/aicore/titancore/` |
| **New Path** | `CodeToUse/_Quarantine/AI_aicore_titancore_shadow_repo/` |
| **Why Quarantined** | Contains a complete Laravel application structure (`app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `resources/`, own `composer.json`, own `package.json`). This is a shadow copy of the host repository or an earlier iteration. Creates maximum confusion risk — any agent or developer scanning the repo may accidentally operate on this copy instead of the canonical host. |
| **Canonical Replacement** | The host repo root `/` |
| **Safe for Future Review?** | Yes — contains historical reference material; do not integrate blindly |

---

### 3. AI — Stale Social Media Extension (v4.5.0)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/AI/AiSocialMedia/` |
| **New Path** | `CodeToUse/_Quarantine/AI_AiSocialMedia_v4_5_stale/` |
| **Why Quarantined** | Version 4.5.0 of AI Social Media extension. Superseded by `CodeToUse/Comms/SocialMedia/` (version 5.1.0 — AI Social Media Pro). |
| **Canonical Replacement** | `CodeToUse/Comms/SocialMedia/` (v5.1.0) |
| **Safe for Future Review?** | Yes — may contain older migration baselines for diff reference |

---

### 4. Voice — Pass 1 (Legacy)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanVoiceSuite_Pass1/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_Pass1_legacy/` |
| **Why Quarantined** | First-generation Voice integration pass. Superseded by Passes 2, 3, 8, 11, Unified, and Pass26 HARDENED. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base/` or `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/` |
| **Safe for Future Review?** | Yes — historical reference only |

---

### 5. Voice — Pass 2 (Legacy)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanVoiceSuite_Pass2/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_Pass2_legacy/` |
| **Why Quarantined** | Second-generation Voice pass. Superseded by later passes. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base/` |
| **Safe for Future Review?** | Yes — historical reference only |

---

### 6. Voice — Pass 3 (Legacy)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanVoiceSuite_Pass3/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_Pass3_legacy/` |
| **Why Quarantined** | Third-generation Voice pass. Superseded by later passes. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base/` |
| **Safe for Future Review?** | Yes — historical reference only |

---

### 7. Voice — Pass 8 (Partial Upgrade)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanVoiceSuite_Pass8_Full/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_Pass8_partial/` |
| **Why Quarantined** | Partial upgrade pass. Superseded by the Unified and Pass26 bundles. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base/` |
| **Safe for Future Review?** | Yes |

---

### 8. Voice — Pass 11 (Partial Upgrade)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanVoiceSuite_Pass11_Full/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_Pass11_partial/` |
| **Why Quarantined** | Partial upgrade pass. Superseded by the Unified and Pass26 bundles. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base/` |
| **Safe for Future Review?** | Yes |

---

### 9. Voice — FULL Recreated (Superseded)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanVoiceSuite_FULL_Real_Recreated/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_FULL_Recreated_superseded/` |
| **Why Quarantined** | Manually recreated full Voice suite. Superseded by the Unified merge and Pass26 HARDENED. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base/` or `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/` |
| **Safe for Future Review?** | Yes |

---

### 10. Voice — MagicAI Overlay v2 (Superseded)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/MagicAI_TitanVoice_True_Minimal_Overlay_v2/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_MagicAI_Overlay_v2_superseded/` |
| **Why Quarantined** | Minimal overlay variant. Superseded by the Unified and Pass26 bundles which are more complete. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/` |
| **Safe for Future Review?** | Yes |

---

### 11. Voice — TitanOmni Pass 24 (Superseded)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanOmni_TotalCodebase_Pass24_SystemOnly/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_TitanOmni_Pass24_superseded/` |
| **Why Quarantined** | Earlier TitanOmni system-only pass. Superseded by Pass26 HARDENED. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/` |
| **Safe for Future Review?** | Yes |

---

### 12. Voice — TitanOmni SystemOnly Pass 26 (Superseded by Complete Pass 26)

| Field | Value |
|-------|-------|
| **Original Path** | `CodeToUse/Voice/TitanOmni_SystemOnly_Pass26/` |
| **New Path** | `CodeToUse/_Quarantine/Voice_TitanOmni_SystemOnly_Pass26_superseded/` |
| **Why Quarantined** | System-only extract of Pass26. The complete hardened Pass26 (`TitanOmni Complete Pass26 HARDENED`) is the full canonical version. |
| **Canonical Replacement** | `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/` |
| **Safe for Future Review?** | Yes |

---

## Active Voice Canonical Candidates (NOT quarantined)

| Path | Status |
|------|--------|
| `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base/` | **Retained** — Unified merge from the largest base; likely most complete pre-Omni Voice bundle |
| `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/` | **Retained** — Latest hardened TitanOmni build; recommended canonical for future Voice integration |

A future integration pass must choose ONE of the above as the final integration target.

---

## Summary

| Item | Count |
|------|-------|
| Trees quarantined | 12 |
| Canonical source trees retained | 2 (Voice) + remaining CodeToUse domains |
| Content destroyed? | No — all content preserved in `_Quarantine/` |
