# ZERO CORE MIGRATION DIFF

Generated: Prompt 1 — Source vs Host Comparison

---

## Overview

This document compares the titancore source (`CodeToUse/aicore/titancore/`) against the current host (`app/`, `config/`, `routes/`) and identifies what is new, what is a conflict, and what is already deployed.

---

## app/ Layer

### Already in Host (No Migration Needed)

| Path | Notes |
|------|-------|
| `app/Titan/Signals/` (full tree) | ✅ Deployed and canonical |
| `app/Providers/TitanSignalsServiceProvider.php` | ✅ Deployed |
| `app/Contracts/TitanIntegration/ZeroSignalBridgeContract.php` | ✅ Deployed |
| `app/Http/Controllers/TitanSignalApiController.php` | ✅ Deployed |
| All CRM domain files | Host-owned, no action |
| All Work domain files | Host-owned, no action |
| All Finance domain files | Host-owned, no action |

### New in Source — Not Yet in Host (Migrate in Prompt 2)

| Path | Type | Risk |
|------|------|------|
| `app/TitanCore/` (full tree, 60+ files) | New feature code | Low — isolated namespace |
| `app/Providers/TitanCoreServiceProvider.php` | New provider | Low — additive |
| `app/Http/Controllers/TitanCore/TitanCoreStatusController.php` | New controller | Low — new route |
| `app/Http/Controllers/TitanCore/` (full tree) | New controllers | Low — new routes |

### Conflicts — Source Overrides Host (Review Before Migrating)

| Source Path | Host Path | Conflict Type | Decision |
|-------------|-----------|---------------|---------|
| `app/Http/Controllers/AiChatbotModelController.php` | Same path | Upgrade conflict — source upgrades to TitanAIRouter | Defer to Prompt 2; host version is canonical until router is wired |
| `app/Http/Controllers/AIChatController.php` | Same path | Duplicate — source is extended version | Defer to Prompt 3; review for merged features |
| `app/Http/Kernel.php` | Same path | Infrastructure duplicate | **Exclude** — host owns Kernel |

### Duplicate Infrastructure — Mark for Exclusion

| Path | Reason |
|------|--------|
| `bootstrap/` | Host-owned |
| `composer.json`, `package.json` | Host-owned dependency manifest |
| `routes/web.php`, `routes/api.php`, `routes/auth.php`, `routes/panel.php` | Host-owned route loading |
| `config/` (all non-Titan files) | Host-owned configs |
| `database/migrations/` (non-Titan) | Host-owned or already applied |

---

## config/ Layer

| Source Config | Host Config | Status |
|--------------|-------------|--------|
| `config/titan_core.php` | Not in host yet | **Migrate in Prompt 2** |
| `config/titan_signal.php` | `config/titan_signal.php` in host | ✅ Same file, no diff needed |

---

## routes/ Layer

| Source Route File | Host Route File | Status |
|------------------|----------------|--------|
| `routes/core/titan_core.routes.php` | Not in host yet | **Migrate in Prompt 2** |
| `routes/core/signals.routes.php` | `routes/core/signals.routes.php` | ✅ Same file in host |
| All other core routes | Same in host | ✅ No change |

Note: Source is missing `routes/core/rewind.routes.php` — host has this as a newer addition.

---

## database/ Layer

| Migration | Status |
|-----------|--------|
| `2026_03_30_220000_create_titan_signal_tables.php` | ✅ Deployed in host |
| All other migrations | Duplicate — host already owns these |

---

## Providers/ Layer

| Source Provider | Host Provider | Status |
|----------------|--------------|--------|
| `TitanSignalsServiceProvider` | ✅ In host `config/app.php` | No change |
| `TitanCoreServiceProvider` | Not yet registered in host | **Register in Prompt 2** |

---

## Views Layer

| Source | Host | Status |
|--------|------|--------|
| `resources/views/default/panel/user/business-suite/core/` | Partially in host | **Migrate/verify in Prompt 2** |
