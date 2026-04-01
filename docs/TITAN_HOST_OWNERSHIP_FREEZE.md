# TITAN HOST OWNERSHIP FREEZE

Generated: Prompt 1 — Host System Ownership Declaration

---

## Purpose

This document declares the systems that the Laravel host already owns. These systems must NOT be duplicated, replaced, or undermined when merging TitanCore. All imported logic must integrate with these systems.

---

## Frozen Host Systems

### 1. CRM Domain

- **Location:** `app/Domains/CRM/`, `routes/core/crm.routes.php`
- **Models:** Customer, Contact, Enquiry, etc. (host-canonical)
- **Status:** Frozen. TitanCore must bridge, not duplicate.
- **TitanCore Access:** Via `titan.crm.*` MCP tools (Prompt 4)

### 2. Work Domain

- **Location:** `app/Domains/Work/`, `routes/core/work.routes.php`
- **Models:** Job (Site), Schedule, ServiceJob, Checklist, etc. (host-canonical, workcore labels)
- **Status:** Frozen. TitanCore must bridge, not duplicate.
- **TitanCore Access:** Via `titan.work.*` MCP tools (Prompt 4)

### 3. Finance Domain

- **Location:** `app/Domains/Finance/`, `routes/core/money.routes.php`
- **Models:** Invoice, Payment, Quote, CreditNote, etc. (host-canonical)
- **Status:** Frozen. TitanCore must bridge, not duplicate.
- **TitanCore Access:** Via `titan.finance.*` MCP tools (Prompt 4)

### 4. Authentication

- **Stack:** Laravel Sanctum / Passport
- **Location:** `app/Http/Controllers/Auth/`, `routes/auth.php`
- **Status:** Frozen. TitanCore uses existing auth middleware and `company_id` tenant scope.

### 5. Tenancy Model

- **Pattern:** `company_id` = tenant boundary; `team_id` = crew grouping
- **Trait:** `App\Models\Concerns\BelongsToCompany`
- **Status:** Frozen. All Titan data (Memory, Signals, Agents, Pulse rules) must include `company_id`.

### 6. Queue System

- **Stack:** Laravel Queue (database/redis driver)
- **Status:** Frozen. TitanCore Jobs must use existing queue infrastructure.

### 7. Blade UI Shell

- **Location:** `resources/views/default/`
- **Theme:** `default` theme with panel layout
- **Status:** Frozen as base. New Titan UI panels inject into existing `business-suite` surface.

### 8. Signal Pipeline

- **Location:** `app/Titan/Signals/`
- **Provider:** `App\Providers\TitanSignalsServiceProvider`
- **Status:** ✅ Deployed and canonical. This is the authoritative signal dispatch system.
- **Includes:** SignalDispatcher, SignalsService, EnvelopeBuilder, ProcessStateMachine, ProcessRecorder, ApprovalChain, AuditTrail, PulseSubscriber, RewindSubscriber, ZeroSubscriber

### 9. Rewind Scaffolding

- **Location:** `routes/core/rewind.routes.php`, Rewind-related controllers
- **Status:** Deployed. TitanCore's RewindManager must hook into this, not replace it.

### 10. AiChatbotModelController

- **Location:** `app/Http/Controllers/AiChatbotModelController.php`
- **Status:** Host-canonical chatbot widget controller.
- **Future Action:** Upgrade in Prompt 2 to route through `TitanAIRouter` (non-destructive upgrade).

### 11. Business-Suite UI Surface

- **Location:** `resources/views/default/panel/user/business-suite/`
- **Routes:** `dashboard.user.business-suite.*`
- **Status:** Frozen as surface structure. New Titan panels add to this surface, not replace it.

### 12. Sanctum / Passport API

- **Location:** `routes/api.php`
- **Status:** Frozen as headless API entry point. MCP tool endpoints will be additive routes.

---

## What TitanCore Owns (Import Targets)

| System | Ownership |
|--------|-----------|
| TitanAIRouter | TitanCore (new — Prompt 2) |
| TitanMemory | TitanCore (new — Prompt 3) |
| Process Lifecycle Bridge | TitanCore bridges to host Signal pipeline |
| Approval Governance | TitanCore extends existing ApprovalChain |
| Pulse Automation Engine | TitanCore new (Pulse subscriber hooks into Signal pipeline) |
| Omni Conversational Layer | TitanCore new |
| Agent Studio Runtime | TitanCore new |
| MCP Bridge | TitanCore new (Prompt 4) |
| Zylos Console Bridge | TitanCore new (Prompt 5) |

---

## Enforcement Rules

1. Never add a new Customer/Job/Invoice model — bridge to host models.
2. Never replace `app/Http/Kernel.php` — it is host-owned.
3. Never replace `config/app.php`, `config/auth.php`, or `config/database.php`.
4. Never add a second auth middleware stack.
5. Never modify the `BelongsToCompany` trait directly — extend it.
6. Signal pipeline (`app/Titan/Signals/`) is canonical — do not duplicate signal dispatch logic.
7. All new Titan providers must be registered additively in `config/app.php` or auto-discovered.
