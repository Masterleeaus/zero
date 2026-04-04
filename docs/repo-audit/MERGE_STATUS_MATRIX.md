# Merge Status Matrix
**Generated:** 2026-04-04 | **Scope:** All known feature areas

---

## Status Definitions

| Status | Meaning |
|--------|---------|
| FULLY_MERGED | Code present, routes wired, migrations present, provider/bootstrapping active |
| PARTIAL | Code present but missing some wiring (routes, events, UI, or migrations incomplete) |
| PR_ONLY | Exists only in PR branches, not in canonical main |
| DOCS_ONLY | Architecture documents exist, no code implementation found |
| LOST | Referenced historically but not found in current canonical branch |
| DUPLICATED | Two or more competing implementations of the same concept |

---

## Matrix

| Feature / System | Domain | Status | Confidence | Notes |
|-----------------|--------|--------|------------|-------|
| **CORE SAAS PLATFORM** | | | | |
| User auth & management | Core | FULLY_MERGED | High | Complete, tested |
| Plans & subscriptions | Core | FULLY_MERGED | High | Stripe, Paddle, etc. |
| Payment gateways (10+) | Core | FULLY_MERGED | High | All major gateways |
| OpenAI content gen | AI | FULLY_MERGED | High | Full pipeline |
| AI Chat | AI | FULLY_MERGED | High | OpenAI chat with rooms |
| ElevenLabs voice | AI | FULLY_MERGED | High | Voice generation |
| FalAI images | AI | FULLY_MERGED | High | Package present |
| Chatbot (embedded) | AI | FULLY_MERGED | High | Full chatbot with crawlers |
| Credit system | Core | FULLY_MERGED | High | Per-user credits |
| Affiliate program | Core | FULLY_MERGED | High | Referral tracking |
| Frontend landing | Core | FULLY_MERGED | High | Section management |
| Themes system | Core | FULLY_MERGED | High | igaster/laravel-theme |
| Telescope | Core | FULLY_MERGED | High | Debug monitoring |
| DeFi market data | Finance | FULLY_MERGED | High | Market/news/portfolio |
| **CRM** | | | | |
| Customer management | CRM | FULLY_MERGED | High | Full model set |
| Deal pipeline | CRM | FULLY_MERGED | High | Deal + DealNote |
| Enquiry management | CRM | FULLY_MERGED | High | Enquiry model |
| CRM-job bridge | CRM | FULLY_MERGED | High | 7 events registered |
| **WORK / FIELD SERVICE** | | | | |
| Service jobs | Work | FULLY_MERGED | High | Full FSM job lifecycle |
| Service agreements | Work | FULLY_MERGED | High | Plan/Visit/Agreement |
| Job stages | Work | FULLY_MERGED | High | Stage transitions |
| FSM kanban/blockers | Work | FULLY_MERGED | High | Module 5 |
| FSM sale pipeline | Work | FULLY_MERGED | High | Module 3 - migration 500100 |
| Sale recurring agreements | Work | FULLY_MERGED | High | Migration 500410 |
| Equipment coverage | Work | FULLY_MERGED | High | Migration 500500 |
| Field service project | Work | FULLY_MERGED | High | Module 22 - migration 500200 |
| Portal (customer) | Work | FULLY_MERGED | High | Module 21 |
| Checklist framework | Work | FULLY_MERGED | High | Migration 000500 |
| HRM / Timesheets | HRM | FULLY_MERGED | High | Migrations 700100, 700200 |
| Leave management | HRM | FULLY_MERGED | High | Work domain models |
| Attendance | HRM | FULLY_MERGED | High | Model present |
| Territory hierarchy | Work | FULLY_MERGED | High | Migrations 200000-200100 |
| Inspection framework | Work | FULLY_MERGED | High | Migration 000400 |
| **DISPATCH** | | | | |
| Dispatch core | Dispatch | FULLY_MERGED | High | DispatchService |
| Easy dispatch (canonical) | Dispatch | FULLY_MERGED | High | 4 new services |
| Dispatch constraints | Dispatch | FULLY_MERGED | High | ConstraintService |
| Dispatch readiness | Dispatch | FULLY_MERGED | High | ReadinessService |
| **ROUTE PLANNING** | | | | |
| Dispatch routes | Route | FULLY_MERGED | High | Migration 400100 |
| Technician availability | Route | FULLY_MERGED | High | Route models |
| Blackout days | Route | FULLY_MERGED | High | Blackout models |
| Scheduling surface | Route | FULLY_MERGED | High | 3 scheduling services |
| **PREMISES** | | | | |
| Premises hierarchy | Premises | FULLY_MERGED | High | Full building/floor/room |
| Hazard tracking | Premises | FULLY_MERGED | High | 2 events + listeners |
| Site assets | Premises | FULLY_MERGED | High | Migration 000300/000600 |
| **EQUIPMENT** | | | | |
| Equipment tracking | Equipment | FULLY_MERGED | High | Migration 300100 |
| Warranty management | Equipment | FULLY_MERGED | High | 10 events |
| **VEHICLES** | | | | |
| Vehicle domain | Vehicle | FULLY_MERGED | High | Migration 500400 |
| Vehicle dispatch | Vehicle | FULLY_MERGED | High | VehicleDispatchService |
| **METERS** | | | | |
| Meter readings | Meter | FULLY_MERGED | High | Migration 000700 |
| **INVENTORY** | | | | |
| Inventory management | Inventory | FULLY_MERGED | High | Migration 700100 |
| Purchase orders | Inventory | FULLY_MERGED | High | Full PO workflow |
| Stock movements | Inventory | FULLY_MERGED | High | Stocktake + movement |
| Supplier management | Inventory | FULLY_MERGED | High | Reused by Finance AP |
| **FINANCE / MONEY** | | | | |
| Invoicing & payments | Finance | FULLY_MERGED | High | Full invoice workflow |
| Quote system | Finance | FULLY_MERGED | High | Quote + templates |
| Chart of accounts | Finance | FULLY_MERGED | High | Migration 600100 |
| Journal entries | Finance | FULLY_MERGED | High | Double-entry ledger |
| AP (supplier bills) | Finance | FULLY_MERGED | High | Migration 600200 |
| Payroll | Finance | FULLY_MERGED | High | Migration 600210 |
| Financial assets | Finance | FULLY_MERGED | High | Migration 600210 |
| Finance reporting | Finance | FULLY_MERGED | High | FinanceReportService |
| Expense management | Finance | FULLY_MERGED | High | Expense + category |
| Credit notes | Finance | FULLY_MERGED | High | CreditNote models |
| **REPAIR** | | | | |
| Repair orders | Repair | FULLY_MERGED | High | Migration 400000 |
| Repair templates | Repair | FULLY_MERGED | High | 4 template models |
| Repair workflow | Repair | FULLY_MERGED | High | 25+ events |
| **SECURITY** | | | | |
| Security domain | Security | FULLY_MERGED | High | Migration 800100 |
| Blacklists | Security | FULLY_MERGED | High | Email/IP blacklists |
| Audit events | Security | FULLY_MERGED | High | SecurityAuditEvent |
| **ADMIN** | | | | |
| Admin user/role/settings | Admin | FULLY_MERGED | High | AdminServiceProvider |
| Admin audit log | Admin | FULLY_MERGED | High | Migration 800100 |
| **TITAN MODULES** | | | | |
| MODULE 01 - TitanDispatch | Titan | FULLY_MERGED | High | 4 new dispatch services |
| MODULE 02 - CapabilityRegistry | Titan | FULLY_MERGED | High | Migration 800200 |
| MODULE 03 - TrustWorkLedger | Titan | FULLY_MERGED | High | Migration 900100 |
| MODULE 04 - TitanContracts | Titan | FULLY_MERGED | High | Migration 900110 |
| MODULE 05 - TitanEdgeSync | Titan | FULLY_MERGED | High | Migration 900120 |
| MODULE 06 - ExecutionTimeGraph | Titan | FULLY_MERGED | High | Migration 900200 |
| MODULE 07 - TitanPredict | Titan | FULLY_MERGED | High | Migration 900300 |
| MODULE 08 - DocsExecutionBridge | Titan | FULLY_MERGED | High | Migration 000800 |
| MODULE 09 - ExecutionFinanceLayer | Titan | FULLY_MERGED | High | Migration 900310 |
| MODULE 10 - TitanMesh | Titan | LOST | High | Marked installed in FSM JSON but NO CODE found |
| **TITANPWA** | | | | |
| PWA device management | PWA | FULLY_MERGED | High | 7 migrations, 10 services |
| PWA signal ingress | PWA | FULLY_MERGED | High | Trust-hardened |
| PWA staged artifacts | PWA | FULLY_MERGED | High | Artifact staging |
| **TITANCORE AI LAYER** | | | | |
| AI memory system | TitanCore | FULLY_MERGED | High | 4 migrations (200001-200004) |
| TitanCore structural skeleton | TitanCore | PARTIAL | Medium | Folders exist, wiring unclear |
| TitanCore/app/Titan duplication | TitanCore | DUPLICATED | High | Two parallel trees |
| TitanCoreConsensus | TitanCore | FULLY_MERGED | High | TriCore + Equilibrium |
| TitanChat bridge | TitanCore | FULLY_MERGED | High | Service + routes |
| Zylos agent layer | TitanCore | PARTIAL | Medium | Folder skeleton exists |
| MCP handlers | TitanCore | PARTIAL | Medium | Files exist, routes registered |
| TitanSignals | TitanCore | FULLY_MERGED | High | Provider + routes |
| **SUPPORT** | | | | |
| Knowledge base | Support | FULLY_MERGED | High | 3 models |
| Notices | Support | FULLY_MERGED | High | Notice + NoticeView |
| Service issues | Support | FULLY_MERGED | High | ServiceIssue + Messages |
| **FSM PENDING MODULES (9-20, 23-30)** | | | | |
| fieldservice_calendar (M09) | FSM | LOST | High | Status: pending |
| fieldservice_recurring (M10) | FSM | LOST | High | Status: pending |
| fieldservice_route_planning (M11) | FSM | PARTIAL | Medium | Route planning exists as Route domain |
| fieldservice_stock (M12) | FSM | PARTIAL | Medium | Inventory domain covers this |
| fieldservice_vehicle (M13) | FSM | FULLY_MERGED | High | Vehicle domain present |
| fieldservice_geoengine (M14) | FSM | LOST | High | Status: pending |
| fieldservice_timeline (M15) | FSM | LOST | High | Status: pending |
| fieldservice_skill (M16) | FSM | PARTIAL | Medium | Capability registry covers skills |
| fieldservice_partner_multi_relation (M17) | FSM | LOST | High | Status: pending |
| fieldservice_account (M18) | FSM | PARTIAL | Medium | Finance domain covers accounting |
| fieldservice_account_analytic (M19) | FSM | LOST | High | Status: pending |
| fieldservice_hr (M20) | FSM | PARTIAL | Medium | HRM domain partially covers |
| fieldservice_repair (M23) | FSM | PARTIAL | Medium | Repair domain exists |
| fieldservice_maintenance (M24) | FSM | LOST | High | Status: pending |
| fieldservice_mrp (M25) | FSM | LOST | High | Status: pending |
| fieldservice_helpdesk (M26) | FSM | PARTIAL | Medium | Support domain covers basics |
| fieldservice_location_geolocalize (M27) | FSM | LOST | High | Status: pending |
| fieldservice_distribution (M28) | FSM | LOST | High | Status: pending |
| fieldservice_change_management (M29) | FSM | LOST | High | Status: pending |
| fieldservice_quality (M30) | FSM | LOST | High | Status: pending |
| **NEXUS / ARCHITECTURE REDESIGN** | | | | |
| 5-mode architecture | Nexus | DOCS_ONLY | High | 145+ docs, zero code |
| Multi-mode entity model | Nexus | DOCS_ONLY | High | Universal grammar designed |
| Route/controller realignment | Nexus | DOCS_ONLY | High | Maps written, not executed |
| Social mode | Nexus | DOCS_ONLY | High | Blueprint only |
| Naming/path canonicalization | Nexus | DOCS_ONLY | High | Planned in 10 phases |
| **OMNI / COMMS** | | | | |
| Omni wiring blueprint | Omni | DOCS_ONLY | High | ZIP file in docs/omni/ |
| Full comms system | Omni | DOCS_ONLY | High | COMMS_TRIAGE_PLAN.md only |
| Social routes | Omni | FULLY_MERGED | High | routes/core/social.routes.php |
| **MOBILE APPS** | | | | |
| TitanCommand app | Mobile | DOCS_ONLY | Low | docs/MOBILE_STACK_ALIGNMENT.md |
| TitanGo app | Mobile | DOCS_ONLY | Low | Referenced in docs only |
| TitanPortal app | Mobile | DOCS_ONLY | Low | Referenced in docs only |
| TitanMoney app | Mobile | DOCS_ONLY | Low | Referenced in docs only |
| TitanPro app | Mobile | DOCS_ONLY | Low | Referenced in docs only |
| **CODETO USE SOURCE BUNDLES** | | | | |
| CodeToUse/AI/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/CRM/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/Comms/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/Dispatch/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/Extensions/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/FSM/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/Finance/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/Node/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/PWA/ | CodeToUse | LOST | High | Directory exists but empty |
| CodeToUse/WorkCore/ | CodeToUse | LOST | High | Directory exists but empty |
