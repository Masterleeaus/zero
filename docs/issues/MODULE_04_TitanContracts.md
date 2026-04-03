# MODULE 04 — TitanContracts: Agreement Entitlement Engine

**Label:** `titan-module` `contracts` `agreements` `entitlement` `billing`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** High

---

## Overview

Build the **TitanContracts** engine — a full agreement lifecycle and entitlement management system that governs what services a customer is entitled to, under what terms, and enforces those entitlements at the point of job creation and billing.

TitanContracts extends the existing `ServiceAgreement` model and `ServicePlan` relationships, adds entitlement checking, SLA enforcement, billing rule derivation, and contract health monitoring. It forms the commercial backbone linking CRM deals to executed work.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Models/Work/ServiceAgreement.php` — understand existing agreement model fully
2. Read `app/Models/Work/ServicePlan.php` — understand how plans reference agreements
3. Read `app/Models/Work/ServiceJob.php` — understand how jobs relate to agreements
4. Read `app/Models/Crm/Customer.php`, `Deal.php` — CRM linkage
5. Read `app/Models/Premises/Premises.php` — premises-level agreement scoping
6. Read `app/Services/Work/AgreementSchedulerService.php` — existing scheduling service
7. Read `app/Events/Crm/` (all 5 events) — CRM event integration pattern
8. Read `database/migrations/` — all agreement, service_plan, service_job table schemas
9. Read `docs/nexuscore/` — scan for contract, agreement, SLA, or entitlement design docs
10. Read `docs/titancore/` — scan for commercial and billing architecture docs

---

## Canonical Models to Extend / Reference

- `app/Models/Work/ServiceAgreement.php` — primary model to extend
- `app/Models/Work/ServicePlan.php` — entitlement consumer
- `app/Models/Work/ServiceJob.php` — entitlement gating point
- `app/Models/Crm/Customer.php` — agreement owner
- `app/Models/Crm/Deal.php` — commercial origin of agreement

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_titan_contracts_tables.php`
  - Extend `service_agreements` table (ALTER, use `hasColumn` guards):
    - `deal_id` (nullable FK to deals)
    - `contract_number` (unique string)
    - `contract_type` (fixed_term|rolling|pay_as_you_go|retainer)
    - `billing_cycle` (monthly|quarterly|annual|per_visit)
    - `billing_amount` (decimal 12,2 nullable)
    - `sla_response_hours` (unsignedSmallInteger nullable)
    - `sla_resolution_hours` (unsignedSmallInteger nullable)
    - `auto_renews` (boolean default false)
    - `renewal_notice_days` (unsignedSmallInteger nullable)
    - `renewed_from_id` (nullable FK self-referential)
    - `health_score` (unsignedTinyInteger nullable — 0–100)
    - `health_flags` (json nullable)
  - `contract_entitlements` — what services are included: `agreement_id`, `service_type`, `max_visits` (nullable), `visits_used`, `max_hours` (nullable), `hours_used`, `period_type` (monthly|quarterly|annual|contract), `resets_on` (date nullable), `is_unlimited` (bool default false)
  - `contract_sla_breaches` — SLA violation log: `company_id`, `agreement_id`, `job_id`, `breach_type` (response|resolution), `sla_hours`, `actual_hours`, `breached_at`, `notified_at` (nullable)
  - `contract_renewals` — renewal history: `agreement_id`, `renewed_to_id`, `renewed_at`, `renewed_by`, `previous_expiry`, `new_expiry`, `notes`
- Use `Schema::hasTable()` / `Schema::hasColumn()` guards throughout

### Models
- `app/Models/Work/ContractEntitlement.php` — with `BelongsTo(ServiceAgreement)`, period reset logic
- `app/Models/Work/ContractSLABreach.php` — with `BelongsTo(ServiceJob)`, `BelongsTo(ServiceAgreement)`
- `app/Models/Work/ContractRenewal.php` — renewal history record
- Extend `app/Models/Work/ServiceAgreement.php` — add new relationships and entitlement helper methods:
  - `entitlements(): HasMany`
  - `slaBreaches(): HasMany`
  - `renewals(): HasMany`
  - `renewedFrom(): BelongsTo` (self)
  - `isActive(): bool`
  - `isExpired(): bool`
  - `isDueForRenewal(int $withinDays = 30): bool`
  - `getHealthScore(): int`

### Services
- `app/Services/Work/ContractEntitlementService.php`
  - `checkEntitlement(ServiceAgreement $agreement, string $serviceType): bool`
  - `consumeEntitlement(ServiceAgreement $agreement, string $serviceType, float $hours = 0): void`
  - `getRemainingEntitlement(ServiceAgreement $agreement, string $serviceType): array`
  - `resetPeriodEntitlements(ServiceAgreement $agreement): void`
  - `getEntitlementSummary(ServiceAgreement $agreement): array`
- `app/Services/Work/ContractSLAService.php`
  - `checkSLAStatus(ServiceJob $job): array` — returns response_breached, resolution_breached
  - `recordBreach(ServiceJob $job, string $breachType, float $actualHours): ContractSLABreach`
  - `getBreachRate(ServiceAgreement $agreement): float`
  - `getAtRiskJobs(int $companyId): Collection` — jobs approaching SLA breach
- `app/Services/Work/ContractHealthService.php`
  - `computeHealthScore(ServiceAgreement $agreement): int` — 0–100 composite score
  - `getHealthFlags(ServiceAgreement $agreement): array`
  - `getUnhealthyContracts(int $companyId): Collection`
- `app/Services/Work/ContractRenewalService.php`
  - `renewContract(ServiceAgreement $agreement, array $renewalData): ServiceAgreement`
  - `getDueForRenewal(int $companyId, int $withinDays = 30): Collection`
  - `autoRenew(ServiceAgreement $agreement): ServiceAgreement`

### Events
- `app/Events/Work/ContractEntitlementExhausted.php`
- `app/Events/Work/ContractSLABreached.php`
- `app/Events/Work/ContractRenewed.php`
- `app/Events/Work/ContractExpired.php`
- `app/Events/Work/ContractHealthDegraded.php`

### Listeners
- `app/Listeners/Work/CheckSLAOnJobStatusChange.php` — fires on job stage transitions
- `app/Listeners/Work/NotifyOnContractExpiry.php`
- `app/Listeners/Work/UpdateContractHealthOnJobCompletion.php`
- `app/Listeners/Crm/LinkAgreementToDeal.php` — fires on `ServiceJobCreatedFromDeal`

### Signals
- Emit via `SignalDispatcher`: `contract.entitlement_checked`, `contract.sla_breached`, `contract.renewed`, `contract.health_degraded`
- Include `agreement_id`, `contract_number`, `company_id` in all signal contexts

### Controllers / Routes
- `app/Http/Controllers/Work/ContractController.php`
  - `entitlements(ServiceAgreement $agreement)`
  - `slaStatus(ServiceAgreement $agreement)`
  - `health(ServiceAgreement $agreement)`
  - `renew(Request $request, ServiceAgreement $agreement)`
  - `renewalQueue(Request $request)` — company-wide renewal pipeline
- Register in `routes/core/work.php` under `contracts` prefix

### Tests
- `tests/Unit/Services/Work/ContractEntitlementServiceTest.php`
- `tests/Unit/Services/Work/ContractSLAServiceTest.php`
- `tests/Feature/Work/ContractControllerTest.php`

### Docs Report
- `docs/modules/MODULE_04_TitanContracts_report.md` — entitlement schema, SLA model, health scoring algorithm, CRM integration map

### FSM Update
- Update `fsm_module_status.json` — set `titan_contracts` to `installed`

---

## Architecture Notes

- `checkEntitlement()` must be called BEFORE a `ServiceJob` can be created under an agreement — gate at service layer
- SLA clock starts when job transitions to `assigned` status (not created) — check `JobStageService` transition timestamps
- Health score is composite: SLA breach rate (40%) + entitlement consumption rate (30%) + renewal proximity (30%)
- `auto_renews` contracts must be processed by a scheduled command — create `app/Console/Commands/ProcessContractRenewals.php`
- Self-referential `renewed_from_id` creates a renewal chain — support traversal in `ContractRenewalService`
- Entitlement periods reset based on `period_type` and `resets_on` date — a scheduled command must handle `resetPeriodEntitlements`
- All entitlement consumption must be within a database transaction to prevent over-consumption race conditions
- Follow existing `AgreementSchedulerService` patterns for scheduled command structure

---

## References

- `app/Models/Work/ServiceAgreement.php`
- `app/Models/Work/ServicePlan.php`
- `app/Models/Work/ServiceJob.php`
- `app/Services/Work/AgreementSchedulerService.php`
- `app/Services/Work/JobStageService.php`
- `app/Events/Crm/` (all 5 events)
- `app/Events/Work/` (all 16 events)
- `app/Titan/Signals/SignalDispatcher.php`
- `app/Titan/Signals/AuditTrail.php`
- `docs/nexuscore/` (contract, SLA, billing docs)
- `docs/titancore/` (commercial architecture docs)
- `CodeToUse/work/` (scan for agreement/contract entity files)
