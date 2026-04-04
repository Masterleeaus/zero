# Migration and Schema Audit
**Generated:** 2026-04-04 | **Total Migrations:** 506

---

## 1. Migration Eras

### Era 1: Base SaaS Platform (2014–2023)
~120 migrations covering:
- Users, password resets, personal access tokens (2014)
- Plans, subscriptions, subscription items (2019)
- Jobs queue, settings (2023-03-01)
- OpenAI generators, chat, filters (2023-03-xx)
- Affiliates, favorites, cache, currencies (2023-04-xx)
- Frontend sections, FAQ, testimonials, blog (2023-05-xx)
- Stripe, MollieGateway, Google, social auth (2023-06 to 2024)
- Voice, video, DeFi, chatbot, knowledge base extensions (2024-2026)

### Era 2: WorkCore Foundation (2026-03-xx)
~80 migrations covering:
- Company, service jobs, agreements, plans, visits
- Teams, zones, sites, customers, CRM
- Premises, vehicles, meters, checklists
- Service areas, territory hierarchy, HRM base

### Era 3: Domain Expansion (2026-04-02)
~25 migrations covering:
- Service plan tables, premises enhancement
- Facility documents, occupancies, site assets
- Inspection domain (TWO versions — collision)
- Checklist framework, meter domain
- Hazard and site access

### Era 4: Module Integration Pass (2026-04-03)
~35 migrations covering:
- PWA system (000100–000700)
- AI memory tables (200001–200004)
- Equipment warranty (300100)
- Repair domain (400000), Dispatch routes (400100)
- FSM sale, portal, kanban columns (500100–500500)
- Finance chart of accounts, AP, payroll (600100–600210)
- Inventory, timesheet submissions (700100)
- Staff profiles (700200)
- Dispatch, security, audit, chat columns (800100 — COLLISION)
- Capability registry (800200)

### Era 5: Advanced Modules (2026-04-03 → 2026-04-04)
~10 migrations covering:
- Trust ledger (900100), contracts (900110), edge sync (900120)
- TimeGraph (900200)
- TitanPredict (900300)
- DocsExecution bridge (000800)
- Finance execution layer (900310)

---

## 2. Timestamp Collision Report

**CRITICAL — Multiple migrations share the same timestamp prefix:**

### 4-WAY COLLISION: 2026_04_03_800100

```
2026_04_03_800100_create_security_domain_tables.php
2026_04_03_800100_create_dispatch_tables.php
2026_04_03_800100_add_titan_chat_surface_columns_to_user_openai_chat.php
2026_04_03_800100_add_company_id_to_tz_audit_log.php
```

**Impact:** Laravel `migrate:fresh` may run these in unpredictable order. If security tables are required by dispatch tables (foreign keys), a race condition exists.

---

### 2-WAY COLLISIONS

```
2026_03_31_100900_create_knowledge_base_tables.php
2026_03_31_100900_add_bank_account_id_to_payments_table.php

2026_03_31_200000_create_service_area_regions_table.php
2026_03_31_200000_create_territory_hierarchy_tables.php

2026_03_31_200100_create_service_area_districts_table.php
2026_03_31_200100_add_territory_to_sites.php

2026_04_02_000100_create_service_plan_tables.php
2026_04_02_000100_enhance_premises_domain.php

2026_04_02_000200_add_premises_to_service_agreements.php
2026_04_02_000200_create_facility_documents_table.php

2026_04_02_000300_create_occupancies_table.php
2026_04_02_000300_create_site_assets_table.php

2026_04_02_000400_create_inspection_domain_tables.php
2026_04_02_000400_create_inspection_tables.php

2026_04_03_700100_create_timesheet_submissions_table.php
2026_04_03_700100_create_inventory_domain_tables.php
```

**Total collisions: 9 timestamp groups with 2+ files = at least 11 extra files needing unique timestamps**

---

## 3. Inspection Migration Duplication

The `2026_04_02_000400` collision is particularly dangerous:

- `create_inspection_domain_tables.php` — likely creates the comprehensive Inspection\* tables
- `create_inspection_tables.php` — likely also creates inspection-related tables

Both files may attempt to create overlapping tables, causing migration failure on `migrate:fresh`.

**Action Required:** Review both files, determine which is canonical, remove or rename the other.

---

## 4. Table Namespace Analysis

### Tables with `tz_` Prefix (TitanZero canonical)

Per TitanZero naming convention, platform-specific tables use `tz_` prefix:

| Table | Migration | Model |
|-------|-----------|-------|
| tz_pwa_devices | 000100 | TzPwaDevice |
| tz_pwa_signal_ingress | 000200 | TzPwaSignalIngress |
| tz_pwa_staged_artifacts | 000700 | TzPwaStagedArtifact |
| tz_ai_memories | 200001 | (TitanCore) |
| tz_ai_memory_embeddings | 200002 | (TitanCore) |
| tz_ai_memory_snapshots | 200003 | (TitanCore) |
| tz_ai_session_handoffs | 200004 | (TitanCore) |
| tz_audit_log | 800100 | AdminAuditLog |

### Legacy Tables (No `tz_` Prefix) — Domain Namespaced

Most WorkCore tables use domain-based names without `tz_` prefix:
- service_jobs, service_agreements, service_plans, service_plan_visits
- customers, customer_contacts, deals, enquiries
- premises, buildings, floors, rooms, units, occupancies
- vehicles, vehicle_assignments, equipment, equipment_warranties
- inspection_instances, inspection_templates
- dispatch_queues, dispatch_routes, dispatch_route_stops
- trust_ledger_entries, trust_chain_seals
- execution_graphs, execution_events
- predictions, prediction_signals
- etc.

**Assessment:** The tz_ prefix is only applied to PWA/AI memory tables. Domain tables use domain-based naming. This is internally consistent.

---

## 5. Schema Drift Analysis

### Models Without Confirmed Migrations

| Model | Migration Evidence | Risk |
|-------|------------------|------|
| app/Models/Work/InspectionInstance.php | Unclear — may use inspection domain migration | MEDIUM |
| app/Models/Facility/SiteAsset.php | Migration 000600 (create_site_asset_tables) | Likely OK |
| app/Models/Work/SiteAsset.php | May conflict with Facility/SiteAsset | MEDIUM |
| app/Models/Work/Territory.php | Migration 200000 (territory_hierarchy) | Likely OK |
| app/Models/Mesh/* | NO migration found | CRITICAL |
| app/Models/Team/AvailabilityWindow.php | Migration 800200 | OK |
| app/Models/Route/AvailabilityWindow.php | Migration — unclear which creates this table | MEDIUM |

### Migrations Without Confirmed Models

| Migration | Table Created | Model Present? |
|-----------|--------------|----------------|
| create_territory_hierarchy_tables | territory_*, region_*, district_* | Work/Territory, Region, District — YES |
| add_territory_to_sites | sites.territory_id | Work/Site — YES |
| create_service_plan_tables (000100 era) | service_plans | Work/ServicePlan — YES |
| enhance_premises_domain (000100 era) | premises additions | Premises/* — YES |

---

## 6. Finance Schema Overlap

The Finance domain has two overlapping namespaces:

| Location | Models | Services |
|----------|--------|---------|
| app/Models/Money/ | Invoice, Payment, Quote, Account, Journal, etc. | TitanMoney/* |
| app/Models/Finance/ | JobCostRecord, JobRevenueRecord, JobFinancialSummary, FinancialRollup | Services/Finance/* |

**Assessment:** These appear to be complementary not competing — Money/ is the AR/AP/accounting layer, Finance/ is the job execution costing layer. Boundary is clear enough.

---

## 7. Migration Sequencing Risks

Potential dependency issues:

| Child Migration | Requires | Risk |
|----------------|---------|------|
| add_premises_to_service_agreements | service_agreements table | Must run after work base |
| create_facility_documents_table | facilities/premises tables | Must run after premises |
| add_fieldservice_sale_recurring_agreement (500410) | service_agreements columns | Must run after 500100 |
| add_fieldservice_sale_agreement_equipment_stock (500500) | equipments, service_agreements | Must run after 500400, 500100 |
| create_trust_ledger_tables (900100) | companies, users | Must run after base |
| create_capability_registry_tables (800200) | teams, users | Must run after work base |

**Sorting by timestamp** ensures proper order for most cases, but 4-way collision at 800100 breaks this guarantee.

---

## 8. Recommended Migration Remediation

### Priority 1 (Critical — Fix Before migrate:fresh)

1. Rename `2026_04_03_800100_create_dispatch_tables.php` → `2026_04_03_800101_create_dispatch_tables.php`
2. Rename `2026_04_03_800100_add_titan_chat_surface_columns_to_user_openai_chat.php` → `2026_04_03_800102_add_titan_chat_surface_columns_to_user_openai_chat.php`
3. Rename `2026_04_03_800100_add_company_id_to_tz_audit_log.php` → `2026_04_03_800103_add_company_id_to_tz_audit_log.php`

### Priority 2 (High — Fix Inspection Duplication)

4. Review and resolve `2026_04_02_000400_create_inspection_domain_tables.php` vs `create_inspection_tables.php`

### Priority 3 (Medium — Resolve Other Collisions)

5. Apply unique sequential suffixes to all remaining timestamp collisions (7 groups)

### Priority 4 (Medium — Clarify Model/Table Boundaries)

6. Resolve AvailabilityWindow duplication (Route vs Team)
7. Resolve SiteAsset duplication (Work vs Facility)
8. Resolve InspectionInstance duplication (Work vs Inspection namespace)

---

## 9. Total Migration Count Summary

| Era | Approx Count | Status |
|-----|-------------|--------|
| Base SaaS (2014-2023) | ~160 | Stable |
| WorkCore Foundation (2026-03) | ~80 | Stable, some collisions |
| Domain Expansion (2026-04-02) | ~25 | 4 collision groups |
| Module Integration (2026-04-03) | ~35 | 4-way collision at 800100 |
| Advanced Modules (2026-04-04) | ~10 | No collisions detected |
| **Total** | **~506** | **9 collision groups = critical risk** |
