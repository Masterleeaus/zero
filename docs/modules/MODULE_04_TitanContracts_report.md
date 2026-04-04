# MODULE 04 — TitanContracts: Agreement Entitlement Engine

**Status:** Installed  
**Date:** 2026-04-03  
**Migration:** `2026_04_03_900100_create_titan_contracts_tables`

---

## Overview

TitanContracts extends the `ServiceAgreement` model with a full agreement lifecycle and entitlement management system. It governs what services a customer is entitled to, under what terms, enforces those entitlements at job creation, monitors SLA compliance, tracks contract health, and automates renewals.

---

## Artifacts Delivered

### Migration

| File | Purpose |
|------|---------|
| `2026_04_03_900100_create_titan_contracts_tables.php` | Extends `service_agreements` (12 new columns); creates `contract_entitlements`, `contract_sla_breaches`, `contract_renewals` |

**New columns on `service_agreements`:**
- `deal_id` — CRM deal linkage
- `contract_number` — unique contract reference
- `contract_type` — fixed_term | rolling | pay_as_you_go | retainer
- `billing_cycle` — monthly | quarterly | annually
- `billing_amount` — contract value
- `sla_response_hours` — response SLA window
- `sla_resolution_hours` — resolution SLA window
- `auto_renews` — whether contract auto-renews
- `renewal_notice_days` — days before expiry to trigger renewal
- `renewed_from_id` — prior agreement reference
- `health_score` — 0–100 computed health score
- `health_flags` — JSON array of health degradation flags

---

### Models

| Model | Table | Purpose |
|-------|-------|---------|
| `ContractEntitlement` | `contract_entitlements` | Per-agreement service type visit/hour caps |
| `ContractSLABreach` | `contract_sla_breaches` | Records of response/resolution SLA breaches |
| `ContractRenewal` | `contract_renewals` | Audit trail of agreement renewals |

**ServiceAgreement extended methods:**
- `deal()` — BelongsTo Deal
- `renewedFrom()` — BelongsTo prior ServiceAgreement
- `entitlements()` — HasMany ContractEntitlement
- `slaBreaches()` — HasMany ContractSLABreach
- `renewals()` — HasMany ContractRenewal
- `isActive()` — existing, retained
- `isExpired()` — check expired_at
- `isDueForRenewal()` — check expiry within renewal_notice_days
- `getHealthScore()` — return health_score int

---

### Services

| Service | Location |
|---------|----------|
| `ContractEntitlementService` | `app/Services/Work/` |
| `ContractSLAService` | `app/Services/Work/` |
| `ContractHealthService` | `app/Services/Work/` |
| `ContractRenewalService` | `app/Services/Work/` |

**ContractEntitlementService:**
- `checkEntitlement(ServiceAgreement, string): bool` — gate check before job creation
- `consumeEntitlement(...): void` — DB-locked consumption preventing race conditions
- `getRemainingEntitlement(...): array` — entitlement status summary
- `resetPeriodEntitlements(...): int` — reset monthly/quarterly/annual caps

**ContractSLAService:**
- `checkSLAStatus(ServiceJob): array` — computes elapsed hours vs SLA window
- `recordBreach(ServiceAgreement, ServiceJob, string, float): ContractSLABreach`
- `getAtRiskJobs(int): Collection` — jobs currently breaching or at risk

**ContractHealthService:**
- `computeHealthScore(ServiceAgreement): int` — 0–100 composite score
  - SLA breach rate: 40 points
  - Entitlement consumption: 30 points
  - Renewal proximity: 30 points
- `refreshHealthScore(ServiceAgreement): int` — persist + fire degraded event
- `getUnhealthyContracts(int): Collection` — agreements below 60 threshold

**ContractRenewalService:**
- `renewContract(ServiceAgreement, array): ServiceAgreement` — create successor agreement
- `getDueForRenewal(int, int): Collection` — agreements due within N days
- `processAutoRenewals(int, int): array` — batch renewal sweep

---

### Console Command

| Command | File |
|---------|------|
| `contracts:process-renewals` | `app/Console/Commands/ProcessContractRenewals.php` |

Options: `--company=`, `--within-days=`

---

### Events

| Event | Trigger |
|-------|---------|
| `ContractEntitlementExhausted` | Entitlement visits/hours reach cap |
| `ContractSLABreached` | SLA response or resolution breach recorded |
| `ContractRenewed` | Agreement successfully renewed |
| `ContractExpired` | Agreement expiry detected at renewal time |
| `ContractHealthDegraded` | Health score crosses below 60 threshold |

---

### Listeners

| Listener | Event | Purpose |
|----------|-------|---------|
| `Work\CheckSLAOnJobStatusChange` | `JobStageChanged` | Detect and record SLA breaches on job state transitions |
| `Work\NotifyOnContractExpiry` | `ContractExpired` | Log/notify on expiry (extendable to Mail/Notifications) |
| `Work\UpdateContractHealthOnJobCompletion` | `JobCompleted` | Recompute health score after job completes |
| `Crm\LinkAgreementToDeal` | `ServiceJobCreatedFromDeal` | Link agreement to CRM deal when job originates from deal |

---

### Controller & Routes

| Route | Method | Action |
|-------|--------|--------|
| `GET /dashboard/work/contracts/renewal-queue` | `renewalQueue` | List agreements due for renewal |
| `GET /dashboard/work/contracts/{agreement}/entitlements` | `entitlements` | Entitlement status per service type |
| `GET /dashboard/work/contracts/{agreement}/sla-status` | `slaStatus` | SLA status for open jobs |
| `GET /dashboard/work/contracts/{agreement}/health` | `health` | Compute and return health score |
| `POST /dashboard/work/contracts/{agreement}/renew` | `renew` | Manual contract renewal |

Route names: `dashboard.work.contracts.*`  
Controller: `App\Http\Controllers\Work\ContractController`

---

### Tests

| Test | Type |
|------|------|
| `ContractEntitlementServiceTest` | Unit |
| `ContractSLAServiceTest` | Unit |
| `ContractControllerTest` | Feature |

---

## Architecture Notes

- `checkEntitlement()` must gate `ServiceJob` creation under any agreement with entitlement records.
- `consumeEntitlement()` uses `DB::transaction` with `lockForUpdate` to prevent over-consumption race conditions.
- Health score formula: SLA breach rate (40%) + entitlement consumption (30%) + renewal proximity (30%).
- SLA clock starts from `assigned_at`, falls back to `scheduled_at`, then `created_at`.
- `auto_renews` contracts are processed by the `contracts:process-renewals` artisan command.
- The `contract_number` column has a unique constraint — generated as `CNT-{8RANDOM}`.

---

## Integration Map

| System | Connection |
|--------|-----------|
| `ServiceAgreement` | Extended with contract columns and new relations |
| `ServiceJob` | SLA clock reads `assigned_at`; jobs link back to agreement |
| `Deal` (CRM) | `deal_id` foreign on service_agreements; listener links on job creation |
| `JobCompleted` event | Triggers health score recomputation |
| `JobStageChanged` event | Triggers SLA breach check |
| `SignalDispatcher` | Extendable via signal keys: `contract.entitlement_checked`, `contract.sla_breached`, `contract.renewed`, `contract.health_degraded` |
