# NEXUS ENGINE DOCUMENTATION — EXECUTIVE SUMMARY & ARCHITECTURAL MAP

**Date:** April 1, 2026  
**Status:** Complete documentation package (94 documents across 16 passes)  
**Audience:** TitanZero founder/lead developer  

---

## I. OVERVIEW

The Nexus Engine is a **five-mode Business Operating System** designed to rebuild TitanZero's core infrastructure from clean, modularized Laravel extensions (MagicAI v10 base).

**Five Canonical Modes:**
1. **Jobs Mode** — Service lifecycle execution (scheduling, checklists, proof, outcomes)
2. **Comms Mode** — Unified conversation layer (inbox, channels, escalation)
3. **Finance Mode** — Revenue visibility (invoices, payments, approvals)
4. **Admin Mode** — System governance (permissions, extensions, audit)
5. **Social Media Mode** — Campaign orchestration & publishing (preserved structure)

**Universal Principle:** All five modes share a common workflow grammar and execution pipeline. Only vocabulary and entity definitions change per mode.

---

## II. CORE ARCHITECTURAL PRINCIPLES

### A. Universal Workflow Grammar

```
Actor creates Item
  ↓
Item belongs to Container
  ↓
Container belongs to Owner
  ↓
Item enters Schedule
  ↓
Schedule triggers Event
  ↓
Event produces Outcome
  ↓
Automation observes Outcome
  ↓
Agent assists Actor
```

This structure is **mode-agnostic**. Each mode substitutes its own entity variables.

### B. Shared Lifecycle

Every action in Nexus passes through:

```
Process → Schedule → Event → Outcome → Signal → Audit
```

---

## III. AGENT HIERARCHY

Nexus operates through a five-layer agent stack with strict authority boundaries:

### **Envoy Layer** (Interface Operators)
- Receive user intent
- Structure draft objects
- Prepare ProcessRecord
- Trigger Mode Decider
- **Cannot:** execute actions, approve automation, modify governance

### **Core Layer** (Orchestration Engine)
- Interpret structured intent
- Coordinate Sentinels
- Assemble execution context
- Manage workflow state graph
- **Never owns domain authority** (Sentinels own it)

### **Sentinel Layer** (Domain Authority)
- Owns mode-specific business logic
- Validates domain constraints
- Executes approved actions
- Emits lifecycle signals
- Each mode has 1 Sentinel

### **Scout Layer** (Automation Engines)
- Detect outcome signals
- Trigger conditional workflows
- Cannot bypass governance gates
- Cannot execute except through Sentinels

### **AEGIS Governance Layer** (Enforcement)
- Attach permission gates at critical transitions
- Prevent silent execution
- Enforce approval pipelines
- Attach audit signals
- Escalate anomalies

---

## IV. MODE DECIDER (Step-0 Classification)

All user intents route through a **mode classifier** that determines:
- Which Sentinel has domain authority
- Which UI overlay applies
- Which tools are accessible
- Which automation rules trigger

**Example classifier inputs:**
```
schedule_job          → Jobs Mode
send_message          → Comms Mode
issue_invoice         → Finance Mode
review_policy         → Admin Mode
create_campaign       → Social Media Mode
```

---

## V. STRUCTURAL