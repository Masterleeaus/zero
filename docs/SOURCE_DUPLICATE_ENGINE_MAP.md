# SOURCE_DUPLICATE_ENGINE_MAP.md

**Phase 8 — Step 9: Duplicate Source Bundle Detection**
**Date:** 2026-04-03
**Scope:** All CodeToUse domains — AI, FSM, WorkCore, Signals, Omni, Routing, Tenancy, Extensions

---

## 1. AI Domain — Duplicate Engines

### 1a. AICores — Duplicate Between AI/AICores and AI/aicore/AICores

`CodeToUse/AI/AICores/` and `CodeToUse/AI/aicore/AICores/` contain **identical directory structures**:

```
ArtCore-main, CommerceCore-main, EdgeChains-ts, aiox-core-main,
laravel-loop-main, laravel-mcp-sdk-main, laravel-rag-main, mcp-main, zylos-core-main
```

**Status: EXACT DUPLICATE** — `AI/aicore/AICores/` is a copy of `AI/AICores/`.

**Recommended action:** Keep `AI/AICores/` as canonical. Remove or archive `AI/aicore/AICores/`.

### 1b. Social Media Extension — Three Parallel Versions

| Location | Extension Name | Version | Status |
|----------|---------------|---------|--------|
| `CodeToUse/AI/AiSocialMedia/` | AI Social Media | 4.5.0 | Older version |
| `CodeToUse/Comms/SocialMedia/` | AI Social Media Pro | 5.1.0 | **Newer version** |
| `CodeToUse/AI/SocialMediaAgent/` | Social Media Agent | 1.2 | Different product (agent-based) |

**Finding:** `AI/AiSocialMedia` and `Comms/SocialMedia` are different versions of the same extension. `AI/AiSocialMedia` (v4.5.0) is superseded by `Comms/SocialMedia` (v5.1.0). The `SocialMediaAgent` is a distinct extension.

**Recommended action:** Use `Comms/SocialMedia` (v5.1.0) as canonical. Archive `AI/AiSocialMedia` (v4.5.0).

### 1c. TitanCore AI Engine — Duplicate

`CodeToUse/AI/aicore/titancore/` contains a full Laravel application structure including:
- `app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `resources/`
- Its own `composer.json`, `package.json`
- Documentation files: `WORKCORE_MERGE.md`, `WORKCORE_RENAME_MAP.md`, `TITAN_CORE_START.md`

This is a **complete copy of the host repository** or an earlier iteration of it.

**Status: SHADOW COPY — high risk of confusion with host codebase.**

---

## 2. Voice Domain — Multiple Pass Copies

| Bundle | Status |
|--------|--------|
| `TitanVoiceSuite_Pass1/` | Legacy pass |
| `TitanVoiceSuite_Pass2/` | Legacy pass |
| `TitanVoiceSuite_Pass3/` | Legacy pass |
| `TitanVoiceSuite_Pass8_Full/` | Partial upgrade |
| `TitanVoiceSuite_Pass11_Full/` | Partial upgrade |
| `TitanVoiceSuite_FULL_Real_Recreated/` | Full recreated |
| `TitanVoiceSuite_Unified_Merged_From_Largest_Base/` | **Likely canonical** |
| `MagicAI_TitanVoice_True_Minimal_Overlay_v2/` | Minimal overlay version |
| `TitanOmni_TotalCodebase_Pass24_SystemOnly/` | Omni-embedded version |
| `TitanOmni Complete Pass26 HARDENED/` | Latest hardened version |
| `TitanOmni_SystemOnly_Pass26/` | System-only extract |

**Status: 11 versions of the Voice Suite exist.** Only ONE should be chosen for integration.

**Recommended canonical candidate:** `TitanVoiceSuite_Unified_Merged_From_Largest_Base/` or `TitanOmni Complete Pass26 HARDENED/` (latest hardened build).

---

## 3. WorkCore Domain — Multiple Versions

| Location | Content |
|----------|---------|
| `CodeToUse/WorkCore/WorkCore/` | Full WorkCore application with MAGICAI_PREMERGE overlay |
| `CodeToUse/WorkCore/work/` | Modular work domain slices (Engineering, Jobs, PM, Provider, Serviceman, Tasks, WorkOrders) |
| `CodeToUse/WorkCore/HRM/` | HRM module |
| `CodeToUse/WorkCore/mobile_app_backend/` | Backend for mobile apps |

**Status:** `WorkCore/WorkCore/` is the full monolith. `WorkCore/work/` contains extracted domain slices. The host has already integrated significant WorkCore functionality (ServiceJob, ServicePlan, FSM modules). These represent earlier/parallel WorkCore versions.

---

## 4. FSM Domain

| Location | Content |
|----------|---------|
| `CodeToUse/FSM/Odoo/` | Odoo-based FSM modules |
| `CodeToUse/FSM/inventory/` | Inventory management module |
| `CodeToUse/FSM/managed-premises/` | Premises management module |

**Note:** Titan Zero host has already merged FSM Modules 1–23. The `CodeToUse/FSM/` bundles appear to be earlier source material or alternative implementations.

**Status:** `FSM/Odoo/` is from a different platform (Odoo). `FSM/inventory/` and `FSM/managed-premises/` may have been the source for the Inventory and Premises modules already integrated into the host.

---

## 5. Signals Domain

| Location | Content |
|----------|---------|
| `CodeToUse/Signals/titan_signal/TitanSignalBase/` | Full TitanSignal architecture with routes, schemas, docs |

**Status:** The host has `TitanSignalsServiceProvider` and `routes/core/signals.routes.php` already integrated. The `CodeToUse/Signals/` bundle is likely the source material for this integration. It should be treated as **already integrated** and **archived**.

---

## 6. Tenancy Domain

| Location | Content |
|----------|---------|
| `CodeToUse/Tenancy/TitanTrust/` | Multi-tenant trust layer |
| `CodeToUse/Tenancy/compliance-auditing/` | Compliance module |
| `CodeToUse/Tenancy/security/` | Security layer |

**Status:** Host has `BelongsToCompany` tenancy trait integrated. TitanTrust and compliance modules are not yet integrated. These are pending integration candidates.

---

## 7. CRM Domain

| Location | Content |
|----------|---------|
| `CodeToUse/CRM/demandium/` | Full Demandium CRM application |
| `CodeToUse/CRM/feedback/Complaint/` | Complaint module |
| `CodeToUse/CRM/feedback/CustomerFeedback/` | Customer feedback module |
| `CodeToUse/CRM/feedback/Feedback/` | Generic feedback |
| `CodeToUse/CRM/feedback/ReviewModule/` | Review module |
| `CodeToUse/CRM/leads/Lead/` | Leads module v1 |
| `CodeToUse/CRM/leads/leads/` | Leads module v2 |

**Status:** Host has `App\Models\Crm\Customer`, `CustomerContact`, `Enquiry`, `Deal` integrated. `CodeToUse/CRM/demandium/` is the original platform — partially integrated. Leads modules appear to have two versions (Lead vs leads directory).

---

## 8. Routing Domain

`CodeToUse/Routing/` is **empty** (no files detected beyond directory).

---

## 9. Extension Library — Potential Same-Extension Duplicates

The following extensions may represent older vs newer versions of the same product (based on name similarity):

| Pair | Likelihood of Duplicate |
|------|------------------------|
| `AIChatPro` + `ChatbotAgent` | HIGH — both are AI chat agents |
| `AiChatProFileChat` + `AiChatProFolders` + `AiChatProMemory` | These are add-ons to `AIChatPro` — not duplicates but companion extensions |
| `ElevenlabsVoiceChat` (ExtensionLibrary) + all Voice passes | HIGH — Voice passes bundle this extension |
| `ChatbotVoice` (ExtensionLibrary) + Voice passes | HIGH |
| `Newsletter` + `MailchimpNewsletter` | MEDIUM — may be different products |
| `Menu` + `MegaMenu` | MEDIUM — MegaMenu likely supersedes Menu |

---

## 10. Summary Table

| Risk Level | Finding |
|------------|---------|
| **CRITICAL** | `CodeToUse/AI/aicore/AICores/` is an exact duplicate of `CodeToUse/AI/AICores/` |
| **CRITICAL** | `CodeToUse/AI/aicore/titancore/` contains a full Laravel app copy — shadow repository risk |
| **CRITICAL** | 11 Voice Suite versions — only ONE should be integrated |
| **HIGH** | `AI/AiSocialMedia` (v4.5.0) is a stale copy superseded by `Comms/SocialMedia` (v5.1.0) |
| **HIGH** | CRM `leads` domain has two parallel versions (`Lead/` and `leads/`) |
| **MEDIUM** | `CodeToUse/FSM/` content may already be integrated in host FSM modules |
| **MEDIUM** | `CodeToUse/Signals/` content appears already integrated — should be archived |
| **LOW** | `CodeToUse/Routing/` is empty |
| **LOW** | Extension Library companion extensions (`AiChatProFolders`, etc.) are not duplicates — they are add-ons |
