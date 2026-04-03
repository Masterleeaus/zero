# DUPLICATE CODE MAP

**Agent:** Copilot  
**Date:** 2026-04-03  
**Purpose:** Detect overlaps between source bundles, CodeToUse extracted domains, and the integrated host application

---

## 1. Already-Integrated Modules (Safe to Reference Only)

These features exist in both `CodeToUse/` (source) and the host `app/` (integrated). The `CodeToUse` versions are reference copies.

| Module | CodeToUse Source | Host Integration | Status |
|--------|-----------------|-----------------|--------|
| FSM Field Service (Sale, Portal, Project, Kanban) | CodeToUse/FSM/ | app/Services/FSM/, app/Models/Work/ | ✓ Integrated |
| FSM Modules 1–23 | CodeToUse/FSM/Odoo/ | app/Services/FSM/ | ✓ Integrated |
| Finance / Chart of Accounts | CodeToUse/Finance/ | app/Models/Money/, app/Services/TitanMoney/ | ✓ Integrated |
| Repair Domain | CodeToUse/FSM/inventory/ | app/Models/Repair/ | ✓ Integrated |
| Dispatch Routes | CodeToUse/Dispatch/ | app/Models/Route/, app/Services/Routing/ | ✓ Integrated |
| HRM Timesheets | CodeToUse/WorkCore/HRM/ | app/Models/Work/TimesheetSubmission | ✓ Integrated |
| Staff Profiles | CodeToUse/WorkCore/HRM/ | app/Models/Work/StaffProfile | ✓ Integrated |
| Inventory Domain | CodeToUse/FSM/inventory/ | app/Models/Inventory/ | ✓ Integrated |
| PWA Service Worker | CodeToUse/PWA/ | public/sw.js, public/pwa-runtime/ | ✓ Integrated |
| PWA Node Trust | CodeToUse/PWA/ | app/Services/TitanZeroPwaSystem/ | ✓ Integrated |
| Titan Core MCP | CodeToUse/AI/aicore/ | app/TitanCore/MCP/ | ✓ Integrated |
| Titan Memory Service | CodeToUse/AI/aicore/ | app/Titan/Core/TitanMemoryService | ✓ Integrated |
| Zylos Bridge | CodeToUse/AI/aicore/ | app/TitanCore/Zylos/ZylosBridge | ✓ Integrated |
| Scheduling Surface | CodeToUse/WorkCore/ | app/Services/Scheduling/ | ✓ Integrated |
| Calendar Adapter | CodeToUse/WorkCore/ | app/Services/Calendar/ | ✓ Integrated |

---

## 2. Partial Overlaps

These modules have partial presence in both source and host, but integration is incomplete.

| Module | CodeToUse Location | Host Partial | Gap |
|--------|-------------------|-------------|-----|
| Social Media (SocialMedia) | CodeToUse/Comms/SocialMedia/ | packages/magicai | Needs route/controller merge |
| AI Social Media (AiSocialMedia) | CodeToUse/AI/AiSocialMedia/ | packages/magicai | Needs social automation wiring |
| Comms / Channels | CodeToUse/Comms/comms/ | app/Services/ partial | Full channel adapter needed |
| TitanBot / Omni | CodeToUse/Omni/ | packages/magicai chatbot | Full Omni adapter merge needed |
| Voice Suite | CodeToUse/Voice/ | partial in Omni | Voice channel merge needed |
| Tenancy / Trust | CodeToUse/Tenancy/TitanTrust/ | app/Models/Concerns/ | Trust model needs merge |
| Security Module | CodeToUse/Tenancy/security/ | Auth stack in host | Review for gaps |
| Leads / CRM | CodeToUse/CRM/leads/ | Not integrated | Phase 2 target |
| Feedback / Reviews | CodeToUse/CRM/feedback/ | Not integrated | Phase 2 target |
| Demandium | CodeToUse/CRM/demandium/ | Not integrated | Phase 2 target |
| Admin UI | CodeToUse/UI/admin/ | resources/views/default/panel/ | Style alignment needed |
| Premises | CodeToUse/FSM/managed-premises/ | partial in FSM | Merge plan exists |

---

## 3. Conflicting Versions

| Component | Version A | Version B | Resolution |
|-----------|-----------|-----------|-----------|
| TitanBot | CodeToUse/Omni/TitanBot (v1) | TitanBot_merged_pass12/13 | Use pass13 (latest) |
| TitanVoiceSuite | Pass1–Pass3 (older) | Pass8/Pass11/Unified (latest) | Use TitanVoiceSuite_Unified |
| TitanOmni System | Pass22/24 | Pass26 HARDENED | Use Pass26 HARDENED |
| AIOX Flows | AIOX-AGENT-FLOWS | AIOX-WORKFLOWS | Both needed (different concerns) |
| docs_titan_adjusted_pass14.zip | Root copy | resources/views copy | Both extracted, source preserved |

---

## 4. Safe Removals (Post-Verification)

The following can be removed after verifying host integration is complete:

| Item | Location | Condition for Removal |
|------|----------|----------------------|
| Older TitanBot passes (pass3, pass6, pass11) | CodeToUse/Omni/ | After pass13 integration verified |
| TitanVoiceSuite Pass1/Pass2/Pass3 | CodeToUse/Voice/ | After Unified merge verified |
| TitanOmni pass22/pass24 | CodeToUse/Voice/ | After pass26 HARDENED verified |
| Duplicate AiSocialMedia folder | CodeToUse/AI/AiSocialMedia/ | After comms integration complete |
| Deprecated tombstones | app/TitanCore/Zero/Memory/ | As noted in TITAN_CORE_FIXPASS_REPORT |

---

## 5. Reuse Candidates

| Candidate | Location | Priority |
|-----------|----------|----------|
| Extension Library (50+ extensions) | CodeToUse/Extensions/ExtensionLibrary/ | High — selectively merge by need |
| Demandium CRM engine | CodeToUse/CRM/demandium/ | High — CRM leads pipeline |
| EasyDispatch engine | CodeToUse/Dispatch/ | Medium — route optimisation |
| Finance Modules | CodeToUse/Finance/ | High — ledger/accounting |
| TitanOmni Pass26 | CodeToUse/Omni/ | High — chatbot/omnichannel |
| TitanVoiceSuite Unified | CodeToUse/Voice/ | High — voice channel |
| WorkCore domain slices | CodeToUse/WorkCore/WorkCore/domain_slices/ | Medium — CRM/HR/Finance slices |
| Hubspot extension | CodeToUse/Extensions/ExtensionLibrary/Hubspot/ | Low — external integration |
| Mailchimp extension | CodeToUse/Extensions/ExtensionLibrary/MailchimpNewsletter/ | Low — marketing |
