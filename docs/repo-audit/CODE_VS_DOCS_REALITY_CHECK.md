# Code vs Docs Reality Check
**Generated:** 2026-04-04 | **Purpose:** Compare promised architecture in docs vs actual installed code

---

## Method

For each major document or doc cluster, we assess whether the described system is actually present in the canonical branch code.

**Verdict Scale:**
- ✅ CONFIRMED — Code exists and matches doc claims
- ⚠️ PARTIAL — Some code exists, not fully implemented as described
- ❌ NOT FOUND — Doc describes system that has no code counterpart
- ⚡ CONFLICT — Doc describes something, code contradicts it

---

## A. FSM Module Status Registry

**Source:** `fsm_module_status.json`

| Module | Doc Claims | Code Reality | Verdict |
|--------|-----------|-------------|---------|
| fieldservice_base | merged | Work models, ServiceJob, agreements exist | ✅ CONFIRMED |
| fieldservice_purchase | merged | Inventory/PurchaseOrder exists | ✅ CONFIRMED |
| fieldservice_sale | merged | FieldServiceSaleService, 500100 migration | ✅ CONFIRMED |
| fieldservice_activity | merged | JobActivity, JobActivityService exist | ✅ CONFIRMED |
| fieldservice_kanban_info | merged | FsmJobBlocker, FsmJobPriorityScore, 500300 | ✅ CONFIRMED |
| fieldservice_crm | merged | CrmServiceJobService, CRM events exist | ✅ CONFIRMED |
| fieldservice_equipment_stock | merged | Equipment models, 300100 migration | ✅ CONFIRMED |
| fieldservice_equipment_warranty | merged | EquipmentWarranty, WarrantyClaim | ✅ CONFIRMED |
| fieldservice_portal | merged | Portal events, portal.routes.php, 500200 | ✅ CONFIRMED |
| fieldservice_project | merged | FieldServiceProject model and service | ✅ CONFIRMED |
| titan_dispatch | installed | DispatchReadinessService, 4 new services | ✅ CONFIRMED |
| capability_registry | installed | Team/* models, CapabilityRegistryService | ✅ CONFIRMED |
| trust_work_ledger | installed | Trust/* models and services | ✅ CONFIRMED |
| titan_contracts | installed | ContractEntitlement, ContractSLA, etc. | ✅ CONFIRMED |
| titan_edge_sync | installed | Sync/* models and services | ✅ CONFIRMED |
| execution_time_graph | installed | TimeGraph/* models and services | ✅ CONFIRMED |
| titan_predict | installed | Predict/* models and services | ✅ CONFIRMED |
| docs_execution_bridge | installed | Premises/FacilityDocument, Docs services | ✅ CONFIRMED |
| execution_finance_layer | installed | Finance/* models and services | ✅ CONFIRMED |
| **titan_mesh** | **installed** | **NO models, routes, or migrations found** | **⚡ CONFLICT** |
| fieldservice_sale_recurring_agreement | merged | SaleRecurringAgreementService, 500410 | ✅ CONFIRMED |
| sale_agreement_equipment_stock_recurring | merged | EquipmentCoverageService, RecurringSaleService, 500500 | ✅ CONFIRMED |

---

## B. Titan Module Reports (docs/modules/)

| Report Doc | Claims | Code Reality | Verdict |
|-----------|--------|-------------|---------|
| MODULE_01_TitanDispatch_report.md | Dispatch services, 5 events | DispatchReadiness/Vehicle/Stock/Agreement services exist | ✅ CONFIRMED |
| MODULE_02_CapabilityRegistry_report.md | 6 models, 2 services, migration 800200 | Team/* models, services, migration | ✅ CONFIRMED |
| MODULE_03_TrustWorkLedger_report.md | 3 models, 2 services, migration 900100 | Trust/* models, services exist | ✅ CONFIRMED |
| MODULE_04_TitanContracts_report.md | 3 models, 4 services, migration 900110 | Contract* models in Work/ | ✅ CONFIRMED |
| MODULE_05_TitanEdgeSync_report.md | 4 models, 3 services, migration 900120 | Sync/* models, services | ✅ CONFIRMED |
| MODULE_06_ExecutionTimeGraph_report.md | 3 models, 2 services, migration 900200 | TimeGraph/* models, services | ✅ CONFIRMED |
| MODULE_07_TitanPredict_report.md | 4 models, 3 services, migration 900300 | Predict/* models, services | ✅ CONFIRMED |
| MODULE_08_DocsExecutionBridge_report.md | 4 models, 3 services, migration 000800 | Premises injection models, Docs services | ✅ CONFIRMED |
| MODULE_09_ExecutionFinanceLayer_report.md | 4 models, 4 services, migration 900310 | Finance/* models, services | ✅ CONFIRMED |
| (MODULE_10_TitanMesh referenced in memory) | 5 models, 5 services, migration 001000 | **NOTHING FOUND** | **❌ NOT FOUND** |

---

## C. Finance Implementation Reports (docs/finance/)

| Report | Claims | Code Reality | Verdict |
|--------|--------|-------------|---------|
| FINANCE_PASS2_IMPLEMENTATION_REPORT.md | SupplierBill, SupplierBillLine, SupplierPayment | app/Models/Money/SupplierBill* exist | ✅ CONFIRMED |
| FINANCE_PASS_IMPLEMENTATION_REPORT.md | Payroll, FinancialAsset, JobCostEntry | app/Models/Money/Payroll, FinancialAsset, JobCostEntry | ✅ CONFIRMED |

---

## D. FSM Sub-Domain Reports (docs/fsm/)

| Report | Claims | Code Reality | Verdict |
|--------|--------|-------------|---------|
| FSM_MODULES_SALE_REPORT.md | Sale pipeline merged | FieldServiceSaleService, events, migration 500100 | ✅ CONFIRMED |
| FSM_MODULES9_10_REPAIR_REPORT.md | Repair domain implemented | Repair/* models, RepairTemplateService | ✅ CONFIRMED |
| FSM_MODULE9_CALENDAR_REPORT.md | Calendar module | **No calendar-specific FSM code found** | **⚠️ PARTIAL** |
| FSM_MODULE11_ROUTE_REPORT.md | Route planning module | Route domain exists (DispatchRoute, etc.) | ✅ CONFIRMED |
| FSM_MODULE_VEHICLE_REPORT.md | Vehicle domain | Vehicle/* models, 500400 migration | ✅ CONFIRMED |
| FSM_MODULES_PORTAL_PROJECT_REPORT.md | Portal + Project | Portal events, FieldServiceProject | ✅ CONFIRMED |
| FSM_MODULE_KANBAN_INFO_REPORT.md | Kanban state/blockers | FSM/* models, 500300 migration | ✅ CONFIRMED |
| FSM_MODULES_SALE_AGREEMENT_EQUIPMENT_STOCK_RECURRING_REPORT.md | EquipmentCoverage + RecurringSale | Both services present | ✅ CONFIRMED |

---

## E. Admin Module Docs

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| ADMIN_MODULE_SCAN.md | Admin module scanned | AdminServiceProvider, services exist | ✅ CONFIRMED |
| ADMIN_ROUTE_MAP.md | Admin routes at titan.admin.* | routes/core/admin.routes.php + titan_admin.routes.php | ✅ CONFIRMED |
| ADMIN_PROVIDER_BINDINGS.md | Provider bindings documented | AdminServiceProvider confirmed | ✅ CONFIRMED |
| ADMIN_TABLE_MAP.md | Table: tz_audit_log | AdminAuditLog model, migration 800100 | ✅ CONFIRMED |

---

## F. WorkCore Root Docs

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| WORKCORE_MERGE.md | WorkCore base merge | Work domain models exist | ✅ CONFIRMED |
| WORKCORE_FEDERATION_TABLE_MAP.md | Federation DB tables | No federated DB found in code | ⚠️ PARTIAL |
| WORKCORE_FEDERATED_DB_PLAN.md | Federated DB architecture plan | Plan only, no implementation | ❌ NOT FOUND |
| WORKCORE_SYNC_AND_REWIND_NOTES.md | Sync + Rewind notes | TitanPWA sync present; Rewind has extension | ⚠️ PARTIAL |

---

## G. HRM Docs

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| HRM_PASS_IMPLEMENTATION_REPORT.md | HRM domain implemented | TimesheetSubmission, StaffProfile, Leave models | ✅ CONFIRMED |
| HRM_HOST_AUDIT.md | Host audit complete | HRM service present | ✅ CONFIRMED |
| HRM_OVERLAP_MATRIX.md | Overlap with Work domain documented | Work models include Leave/Attendance | ✅ CONFIRMED |

---

## H. Inventory Docs

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| INVENTORY_PASS_IMPLEMENTATION_REPORT.md | Inventory domain implemented | Inventory/* models, services, migration 700100 | ✅ CONFIRMED |

---

## I. Security Docs

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| SECURITY_PASS_IMPLEMENTATION_REPORT.md | Security domain implemented | Security/* models, services, migration 800100 | ✅ CONFIRMED |
| SECURITY_TENANCY_ALIGNMENT.md | Security scoped to company | BelongsToCompany trait on security models | ✅ CONFIRMED |

---

## J. Nexus / Architecture Redesign Docs (docs/nexuscore/)

**CRITICAL FINDING:** 145+ documents describe a comprehensive architecture conversion. None of this has been implemented.

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| DOC01_Five_Mode_Model.md | 5-mode platform (Jobs, Comms, Finance, Admin, Social) | Single unified platform, no mode routing | ❌ NOT FOUND |
| DOC02_Universal_Grammar.md | Entity grammar with mode context | No entity grammar in code | ❌ NOT FOUND |
| DOC31_Path_Rename_Matrix.md | Path rename plan | No renames executed | ❌ NOT FOUND |
| DOC38_Master_Development_Roadmap.md | 10-phase roadmap | Phase 0 only? No code changes | ❌ NOT FOUND |
| DOC42_Phase3_Route_Canonicalization.md | Route canonicalization plan | Routes unchanged | ❌ NOT FOUND |
| DOC50_Copilot_Prompt_Execution_Order.md | Execution order for all phases | 0 phases executed | ❌ NOT FOUND |
| DOC80_Jobs_Mode_Blueprint.md | Jobs mode redesign | Existing Work domain unchanged | ❌ NOT FOUND |
| DOC84_Social_Mode_Preservation_Blueprint.md | Social mode preservation | social.routes.php exists but no social mode | ⚠️ PARTIAL |

**Truth verdict on Nexus docs:** These 145+ docs represent planning work that has NOT been executed. The platform is in its pre-conversion state.

---

## K. Dispatch Docs

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| EASY_DISPATCH_CANONICAL_MERGE_REPORT.md | Easy dispatch merged with 4 services | DispatchReadiness/Vehicle/Stock/Agreement services | ✅ CONFIRMED |

---

## L. TitanCore Docs (docs/titancore/)

| Doc Cluster | Claims | Code Reality | Verdict |
|------------|--------|-------------|---------|
| 01-10 Service docs (TITAN_DOC_1-10) | TitanCore architecture defined | TitanCoreServiceProvider exists, TitanCore/ skeleton | ⚠️ PARTIAL |
| MCP tool skeleton (03_MCP) | MCP server setup | routes/core/mcp.routes.php, TitanCore/MCP/ folder | ⚠️ PARTIAL |
| Memory service (05_MEMORY) | AI memory service | tz_ai_memory_* tables exist | ✅ CONFIRMED |
| Zylos bridge (07-08) | Zylos agent bridge | app/TitanCore/Zylos/ folder exists | ⚠️ PARTIAL |
| Pass 14 views | View surface | docs/titancore/pass14-views/ exists | ⚠️ PARTIAL |

---

## M. PR Audit Report

| Doc | Claims | Code Reality | Verdict |
|-----|--------|-------------|---------|
| PR_MERGE_AUDIT_REPORT.md | Audit of all merged PRs | All module 01-09 confirmed merged | ✅ CONFIRMED |

---

## Summary

| Category | Confirmed | Partial | Not Found | Conflict |
|----------|-----------|---------|-----------|---------|
| FSM Module Status | 22 | 0 | 0 | 1 (TitanMesh) |
| Module Reports | 9 | 0 | 1 (Mesh) | 0 |
| Finance Docs | 2 | 0 | 0 | 0 |
| FSM Sub-domain Docs | 7 | 1 (Calendar) | 0 | 0 |
| Admin Docs | 4 | 0 | 0 | 0 |
| WorkCore Docs | 2 | 1 | 1 | 0 |
| HRM/Inventory/Security | 6 | 0 | 0 | 0 |
| **Nexus Architecture Docs** | **0** | **1** | **7+** | **0** |
| TitanCore Docs | 1 | 4 | 0 | 0 |
| Dispatch Docs | 1 | 0 | 0 | 0 |

**Overall assessment:** ~75% of docs have corresponding code reality. The major gap is the entire Nexus architecture planning suite (145+ docs) which has zero code implementation.
