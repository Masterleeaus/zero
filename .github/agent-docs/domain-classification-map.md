# DOMAIN CLASSIFICATION MAP

**Agent:** Copilot  
**Date:** 2026-04-03  
**Purpose:** Map features, modules, tables, providers, and integration targets to their canonical domains

---

## 1. Feature → Domain Mapping

| Feature / Module | Domain | CodeToUse Path |
|-----------------|--------|---------------|
| AI completions, skill execution | AI | CodeToUse/AI/ |
| Social media automation | AI | CodeToUse/AI/AiSocialMedia/ |
| AI Social Media Agent | AI | CodeToUse/AI/SocialMediaAgent/ |
| AIOX Core engine | AI | CodeToUse/AI/AICores/ |
| Social media management | Comms | CodeToUse/Comms/SocialMedia/ |
| Comms channels (email, push, SMS) | Comms | CodeToUse/Comms/comms/ |
| Real-time messaging (Ably) | Comms | CodeToUse/Comms/ably-archive/ |
| Leads management | CRM | CodeToUse/CRM/leads/ |
| Feedback & reviews | CRM | CodeToUse/CRM/feedback/ |
| Demand generation (Demandium) | CRM | CodeToUse/CRM/demandium/ |
| Route dispatch/optimisation | Dispatch | CodeToUse/Dispatch/ |
| Invoices, ledger, chart of accounts | Finance | CodeToUse/Finance/ |
| Field service management | FSM | CodeToUse/FSM/ |
| Managed premises | FSM | CodeToUse/FSM/managed-premises/ |
| Odoo FSM bundles | FSM | CodeToUse/FSM/Odoo/ |
| Inventory / stock management | FSM | CodeToUse/FSM/inventory/ |
| TitanCommand mobile app | Mobile | CodeToUse/Mobile/TitanCommand/ |
| TitanGo mobile app | Mobile | CodeToUse/Mobile/TitanGo/ |
| TitanPortal mobile app | Mobile | CodeToUse/Mobile/TitanPortal/ |
| TitanMoney mobile app | Mobile | CodeToUse/Mobile/TitanMoney/ |
| TitanPro mobile app | Mobile | CodeToUse/Mobile/TitanPro/ |
| Node mesh, device identity | Node | CodeToUse/Node/ |
| Omni-channel chatbot | Omni | CodeToUse/Omni/ |
| TitanBot (all passes) | Omni | CodeToUse/Omni/TitanOmni/ |
| Offline sync, service workers | PWA | CodeToUse/PWA/ |
| Client-side runtime | PWA | CodeToUse/PWA/platform/ |
| Route manifest, named routes | Routing | CodeToUse/Routing/ |
| Job scheduling, calendar | Scheduling | CodeToUse/Scheduling/ |
| Signals, automation, triggers | Signals | CodeToUse/Signals/ |
| Titan Signal engine | Signals | CodeToUse/Signals/titan_signal/ |
| Multi-tenancy, company isolation | Tenancy | CodeToUse/Tenancy/ |
| Compliance & auditing | Tenancy | CodeToUse/Tenancy/compliance-auditing/ |
| TitanTrust | Tenancy | CodeToUse/Tenancy/TitanTrust/ |
| Security module | Tenancy | CodeToUse/Tenancy/security/ |
| Admin UI, panels | UI | CodeToUse/UI/ |
| Tambo (A2UI + classic) | UI | CodeToUse/UI/Tambo/ |
| Utility helpers, tools | Utilities | CodeToUse/Utilities/ |
| Voice channels, TitanVoiceSuite | Voice | CodeToUse/Voice/ |
| Jobs, timesheets, workforce | WorkCore | CodeToUse/WorkCore/ |
| HRM, staff profiles | WorkCore | CodeToUse/WorkCore/HRM/ |
| WorkCore domain slices | WorkCore | CodeToUse/WorkCore/WorkCore/ |
| Platform extensions (50+) | Extensions | CodeToUse/Extensions/ |
| Zero core, system foundation | Nexus | CodeToUse/Nexus/ |

---

## 2. Module → Destination Folder

| Source Module | Destination |
|--------------|-------------|
| `aicore/` | `CodeToUse/AI/aicore/` |
| `AiSocialMedia/` | `CodeToUse/AI/AiSocialMedia/` |
| `SocialMediaAgent/` | `CodeToUse/AI/SocialMediaAgent/` |
| `AICores/` | `CodeToUse/AI/AICores/` |
| `SocialMedia/` | `CodeToUse/Comms/SocialMedia/` |
| `comms/` | `CodeToUse/Comms/comms/` |
| `leads/` | `CodeToUse/CRM/leads/` |
| `feedback/` | `CodeToUse/CRM/feedback/` |
| `demandium/` | `CodeToUse/CRM/demandium/` |
| `easydispatch-main/` | `CodeToUse/Dispatch/easydispatch-main/` |
| `FinanceModules/` | `CodeToUse/Finance/FinanceModules/` |
| `managed-premises/` | `CodeToUse/FSM/managed-premises/` |
| `Odoo/` | `CodeToUse/FSM/Odoo/` |
| `inventory/` | `CodeToUse/FSM/inventory/` |
| `TitanCommand/` | `CodeToUse/Mobile/TitanCommand/` |
| `TitanGo/` | `CodeToUse/Mobile/TitanGo/` |
| `TitanPortal/` | `CodeToUse/Mobile/TitanPortal/` |
| `TitanMoney/` | `CodeToUse/Mobile/TitanMoney/` |
| `TitanPro/` | `CodeToUse/Mobile/TitanPro/` |
| `TitanOmni/` | `CodeToUse/Omni/TitanOmni/` |
| `platform/` | `CodeToUse/PWA/platform/` |
| `titan_signal/` | `CodeToUse/Signals/titan_signal/` |
| `compliance-auditing/` | `CodeToUse/Tenancy/compliance-auditing/` |
| `TitanTrust/` | `CodeToUse/Tenancy/TitanTrust/` |
| `security/` | `CodeToUse/Tenancy/security/` |
| `admin/` | `CodeToUse/UI/admin/` |
| `Tambo/` | `CodeToUse/UI/Tambo/` |
| `utilities/` | `CodeToUse/Utilities/utilities/` |
| `Voice ZIPs (11 passes)` | `CodeToUse/Voice/` |
| `work/` | `CodeToUse/WorkCore/work/` |
| `HRM/` | `CodeToUse/WorkCore/HRM/` |
| `WorkCore/` | `CodeToUse/WorkCore/WorkCore/` |
| `mobile_app_backend/` | `CodeToUse/WorkCore/mobile_app_backend/` |
| `ExtensionLibrary/` | `CodeToUse/Extensions/ExtensionLibrary/` |
| `zero_core/` | `CodeToUse/Nexus/zero_core/` |

---

## 3. Database Tables → Subsystem

| Table Prefix | Subsystem | Domain |
|-------------|-----------|--------|
| `tz_pwa_*` | PWA client runtime | PWA |
| `tz_node_*` | Node mesh/device sync | Node |
| `service_jobs`, `service_plans`, `job_stages` | FSM Field Service | FSM |
| `field_service_*`, `fsm_*` | FSM | FSM |
| `repair_orders`, `repair_*` | Repair | FSM |
| `dispatch_routes`, `route_*` | Routing | Routing |
| `accounts`, `journal_*`, `ledger_*` | Finance | Finance |
| `invoices`, `invoice_*`, `payments`, `quotes` | Finance | Finance |
| `timesheet_submissions` | HRM | WorkCore |
| `staff_profiles` | HRM | WorkCore |
| `inventory_items`, `warehouses`, `stock_*`, `suppliers` | Inventory | FSM |
| `purchase_orders`, `purchase_order_items` | Inventory | FSM |
| `titan_memory`, `titan_signals`, `titan_*` | Titan Core | AI/Nexus |
| `tz_pwa_ingress`, `tz_pwa_device_*`, `tz_pwa_staged_*` | PWA | PWA |
| `companies`, `users`, `teams` | Tenancy/Auth | Tenancy |
| `customers`, `contacts` | CRM | CRM |

---

## 4. Providers → Integration Targets

| Provider | Domain | Binds |
|----------|--------|-------|
| `TitanCoreServiceProvider` | AI/Nexus | TitanMemoryService, ZylosBridge, contracts |
| `RouteServiceProvider` | Routing | All route files under routes/core/*.routes.php |
| `AppServiceProvider` | Core | Global service bindings |
| `TenancyServiceProvider` | Tenancy | Company isolation, BelongsToCompany |
| Domain service providers (FSM, Finance, HRM, etc.) | Domain-specific | Domain models + services |
| `PwaServiceProvider` (if present) | PWA | PWA runtime, ingress, device trust |

---

## 5. Node vs PWA Separation

### Node Domain (`CodeToUse/Node/`)
- `tz_node_*` schema tables
- Mesh sync logic
- Edge compute engines
- Device identity / fingerprinting
- Federated git logic
- Peer routing

### PWA Domain (`CodeToUse/PWA/`)
- `tz_pwa_*` schema tables
- Service workers (`public/sw.js`)
- Offline storage / IndexedDB
- Runtime caching
- Manifest logic (`manifest.json`)
- Client-side persistence
- Staged artifacts

---

## 6. docs/ Topic Classification

| Topic | Subfolder | Files |
|-------|-----------|-------|
| Titan Core architecture | `docs/titancore/` | ~121 files |
| Nexus Engine | `docs/nexuscore/` | ~113 files |
| FSM / Field Service | `docs/fsm/` | 4 files |
| Finance / Money | `docs/finance/` | 6 files |
| Routes | `docs/routes/` | 4 files |
| Zero Core | `docs/zero/` | 3 files |
| Core domain plans | `docs/core/` | 9 files |
| Merge reports | `docs/merge_reports/` | 8 files |
| GitHub issue modules | `docs/issues/` | 10 files |
| Root-level (misc) | `docs/` | ~14 files |
