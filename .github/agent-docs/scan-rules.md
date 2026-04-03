# SCAN REPORT — Repository Cleanup & Domain Sorting

**Agent:** Copilot  
**Date:** 2026-04-03  
**Task:** Deep-scan and reorganise CodeToUse/ and docs/ into domain-structured architecture

---

## 1. Repository Root Scan

| Item | Status |
|------|--------|
| `app/` — Laravel core application | Integrated |
| `CodeToUse/` — Source bundles (pre-sort) | Reorganised ✓ |
| `docs/` — Documentation | Reorganised ✓ |
| `mobile_apps/` — Mobile app source | Copied to CodeToUse/Mobile/ ✓ |
| `routes/` — Laravel route files | Integrated |
| `resources/` — Views, assets | Integrated |
| `database/migrations/` | Integrated |
| `packages/` — Composer sub-packages | Integrated |
| Root-level `.zip` files | Extracted + deleted ✓ |
| Root-level `.md` plan files | Preserved at root |

---

## 2. Detected Domains

| Domain | Source Bundles | Files |
|--------|---------------|-------|
| **AI** | aicore, AICores, AiSocialMedia, SocialMediaAgent | ~14,637 |
| **Comms** | SocialMedia, comms, ably-archive | ~5,021 |
| **CRM** | leads, feedback, demandium | ~5,269 |
| **Dispatch** | easydispatch-main | ~474 |
| **Finance** | FinanceModules | ~1,046 |
| **FSM** | managed-premises, Odoo fieldservice, inventory | ~2,674 |
| **Mobile** | TitanCommand, TitanGo, TitanPortal, TitanMoney, TitanPro | ~4,311 |
| **Omni** | TitanOmni, TitanBot, TitanHello, MarketingBot | ~4,348 |
| **PWA** | platform | ~2,363 |
| **Signals** | titan_signal | ~46 |
| **Tenancy** | compliance-auditing, TitanTrust, security | ~1,597 |
| **UI** | admin, Tambo | ~1,560 |
| **Utilities** | utilities | ~487 |
| **Voice** | TitanVoiceSuite (all passes), MagicAI voice | ~2,720 |
| **WorkCore** | work, HRM, WorkCore root bundle | ~9,781 |
| **Extensions** | ExtensionLibrary (50+ extensions) | ~2,942 |
| **Nexus** | zero_core | ~6,175 |

---

## 3. ZIP Files Processed

All ZIP files have been extracted and deleted per cleanup policy.

| ZIP File | Domain | Action |
|----------|--------|--------|
| `CodeToUse/AICores.zip` | AI | Extracted → CodeToUse/AI/AICores/ ✓ |
| `CodeToUse/AiSocialMedia.zip` | AI | Extracted (duplicate of AiSocialMedia/) ✓ |
| `CodeToUse/ExtensionLibrary.zip` | Extensions | Extracted → CodeToUse/Extensions/ ✓ |
| `CodeToUse/FinanceModules.zip` | Finance | Extracted → CodeToUse/Finance/ ✓ |
| `CodeToUse/HRM.zip` | WorkCore | Extracted → CodeToUse/WorkCore/HRM/ ✓ |
| `CodeToUse/SocialMedia.zip` | Comms | Extracted (duplicate of SocialMedia/) ✓ |
| `CodeToUse/SocialMediaAgent.zip` | AI | Extracted (duplicate of SocialMediaAgent/) ✓ |
| `CodeToUse/Tambo_usable_code_extract_plus_A2UI_plus_classic.zip` | UI | Extracted → CodeToUse/UI/Tambo/ ✓ |
| `CodeToUse/Titan Omni.zip` | Omni | Extracted → CodeToUse/Omni/TitanOmni/ ✓ |
| `CodeToUse/TitanTrust.zip` | Tenancy | Extracted → CodeToUse/Tenancy/TitanTrust/ ✓ |
| `CodeToUse/admin.zip` | UI | Extracted → CodeToUse/UI/admin/ ✓ |
| `CodeToUse/comms.zip` | Comms | Extracted → CodeToUse/Comms/comms/ ✓ |
| `CodeToUse/compliance&auditing.zip` | Tenancy | Extracted (duplicate of compliance-auditing/) ✓ |
| `CodeToUse/demandium.zip` | CRM | Extracted → CodeToUse/CRM/demandium/ ✓ |
| `CodeToUse/easydispatch-main.zip` | Dispatch | Extracted → CodeToUse/Dispatch/ ✓ |
| `CodeToUse/feedback&reviews.zip` | CRM | Extracted (duplicate of feedback/) ✓ |
| `CodeToUse/inventory.zip` | FSM | Extracted → CodeToUse/FSM/inventory/ ✓ |
| `CodeToUse/leads.zip` | CRM | Extracted (duplicate of leads/) ✓ |
| `CodeToUse/managedpremises.zip` | FSM | Extracted (duplicate of managed-premises/) ✓ |
| `CodeToUse/platform.zip` | PWA | Extracted → CodeToUse/PWA/platform/ ✓ |
| `CodeToUse/security.zip` | Tenancy | Extracted → CodeToUse/Tenancy/security/ ✓ |
| `CodeToUse/titan_signal.zip` | Signals | Extracted → CodeToUse/Signals/titan_signal/ ✓ |
| `CodeToUse/utilities.zip` | Utilities | Extracted (duplicate of utilities/) ✓ |
| `CodeToUse/work.zip` | WorkCore | Extracted (duplicate of work/) ✓ |
| `CodeToUse/zero_core.zip` | Nexus | Extracted → CodeToUse/Nexus/zero_core/ ✓ |
| `Archive.zip` (root) | Comms | Extracted → CodeToUse/Comms/ably-archive/ ✓ |
| `WorkCore.zip` (root) | WorkCore | Extracted → CodeToUse/WorkCore/WorkCore/ ✓ |
| `docs_titan_adjusted_pass14.zip` (root) | Docs | Extracted → docs/titancore/pass14/ ✓ |
| `docs/nexuscore/Nexus_Engine_Docs_*.zip` | Nexus | Extracted → docs/nexuscore/engine-docs-pass1-16/ ✓ |
| `docs/nexuscore/Titan Nexus Docs.zip` | Nexus | Extracted → docs/nexuscore/ ✓ |

---

## 4. Already-Integrated Subsystems

These subsystems have been merged into the host `app/` layer and their CodeToUse sources are reference copies only:

| Subsystem | Host Location | CodeToUse Source |
|-----------|--------------|-----------------|
| FSM Field Service | `app/Models/Work/`, `app/Services/FSM/` | CodeToUse/FSM/ |
| Finance / Money | `app/Models/Money/`, `app/Services/TitanMoney/` | CodeToUse/Finance/ |
| Repair Domain | `app/Models/Repair/` | CodeToUse/FSM/ |
| Dispatch Routes | `app/Models/Route/` | CodeToUse/Dispatch/ |
| HRM (Timesheets, Staff) | `app/Models/Work/`, `app/Services/HRM/` | CodeToUse/WorkCore/HRM/ |
| Inventory | `app/Models/Inventory/` | CodeToUse/FSM/inventory/ |
| PWA Runtime | `public/sw.js`, `app/Services/TitanZeroPwaSystem/` | CodeToUse/PWA/ |
| Titan Core / MCP | `app/TitanCore/`, `app/Titan/` | CodeToUse/AI/aicore/ |
| Scheduling Surface | `app/Services/Scheduling/` | CodeToUse/WorkCore/ |

---

## 5. Duplicate Bundles Detected

| Bundle | Duplicate | Recommendation |
|--------|-----------|---------------|
| `AiSocialMedia.zip` | `AiSocialMedia/` folder | ZIP deleted, folder kept |
| `SocialMedia.zip` | `SocialMedia/` folder | ZIP deleted, folder kept |
| `SocialMediaAgent.zip` | `SocialMediaAgent/` folder | ZIP deleted, folder kept |
| `compliance&auditing.zip` | `compliance-auditing/` folder | ZIP deleted, folder kept |
| `feedback&reviews.zip` | `feedback/` folder | ZIP deleted, folder kept |
| `leads.zip` | `leads/` folder | ZIP deleted, folder kept |
| `managedpremises.zip` | `managed-premises/` folder | ZIP deleted, folder kept |
| `utilities.zip` | `utilities/` folder | ZIP deleted, folder kept |
| `work.zip` | `work/` folder | ZIP deleted, folder kept |

---

## 6. Reusable Engines Identified

| Engine | Source | Domain |
|--------|--------|--------|
| TitanVoiceSuite (11 passes) | CodeToUse/Voice/ | Voice |
| TitanBot (13 passes) | CodeToUse/Omni/ | Omni |
| AIOX Core | CodeToUse/AI/AICores/ | AI |
| EasyDispatch | CodeToUse/Dispatch/ | Dispatch |
| Demandium CRM | CodeToUse/CRM/demandium/ | CRM |
| Finance Modules | CodeToUse/Finance/ | Finance |
| WorkCore Domain Slices | CodeToUse/WorkCore/WorkCore/domain_slices/ | WorkCore |
| Extension Library (50+) | CodeToUse/Extensions/ExtensionLibrary/ | Extensions |

---

## 7. Legacy ZIPs Deleted

All ZIPs listed in Section 3 have been deleted after successful extraction. No ZIPs remain in any directory (verified).

---

## 8. Mobile Apps Migration

All mobile apps copied from `mobile_apps/` to `CodeToUse/Mobile/`:

- `mobile_apps/TitanCommand` → `CodeToUse/Mobile/TitanCommand`
- `mobile_apps/TitanGo` → `CodeToUse/Mobile/TitanGo`
- `mobile_apps/TitanPortal` → `CodeToUse/Mobile/TitanPortal`
- `mobile_apps/TitanMoney` → `CodeToUse/Mobile/TitanMoney`
- `mobile_apps/TitanPro` → `CodeToUse/Mobile/TitanPro`

Original `mobile_apps/` directory preserved (source remains intact).
