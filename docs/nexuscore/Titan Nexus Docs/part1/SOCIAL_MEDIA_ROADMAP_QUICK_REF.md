# SOCIAL MEDIA × NEXUS — QUICK-REFERENCE ROADMAP

**Status:** Current module v4.5.0 → Nexus-aligned v5.0  
**Effort:** 3–4 weeks | 5 phased bundles | 40+ deliverables

---

## ONE-PAGE SUMMARY

### Current State
- **Entity:** 6 models, 8 tables, 3 platform services (Instagram, Twitter, LinkedIn)
- **Architecture:** Pre-Nexus (no Sentinel, no Signal backbone, no governance gates)
- **Controllers:** AutomationController + 4 support controllers (mixed responsibilities)
- **Routes:** `dashboard.user.automation.*` (38 routes, scattered concerns)
- **Status:** Functionally complete; ready for realignment

### Target State (Nexus v5.0)
- **Entity:** Post → PublishEvent → AnalyticsOutcome (normalized)
- **Architecture:** SocialMediaSentinel (domain authority) + AEGIS governance
- **Controllers:** 5 focused controllers by function (Campaign, Post, Account, Analytics, Workflow)
- **Routes:** `dashboard.user.social.*` (canonical, organized)
- **Signals:** 8 lifecycle signals (post.drafted, post.scheduled, post.published, post.failed, metrics.updated, campaign.created, campaign.started, campaign.completed)

---

## PHASE OVERVIEW

### BUNDLE A: Structural Classification (Days 1–2)
**Deliverables:** Entity lock, terminology registry, ambiguity list

```
Outputs:
✓ social_entity_lock.md
✓ social_terminology_registry.md
✓ ambiguous_terms_flagged.md
```

**Key Decisions:**
- Confirm: Platform → Account → Campaign → Post hierarchy
- Lock: "Campaign" = marketing initiative, not automation rule
- Lock: "Post" = content draft (not scheduled, not published)
- Lock: "Schedule" = repeating rule (separate entity)

---

### BUNDLE B: Naming Canonicalization (Days 3–5)
**Deliverables:** Route map, controller matrix, view relocation guide

```
Route Changes:
  dashboard.user.automation.* (38 routes)
    → dashboard.user.social.posts.*
    → dashboard.user.social.campaigns.*
    → dashboard.user.social.accounts.*
    → dashboard.user.social.analytics.*

Controller Changes:
  AutomationController (80+ methods)
    → CampaignController (CRUD campaigns)
    → PostController (CRUD posts)
    → AccountController (connect/disconnect platforms)
    → AnalyticsController (view metrics)
    → WorkflowController (step-by-step wizard)

View Changes:
  resources/views/automation-steps/
    → resources/views/workflows/
  resources/views/campaigns/
    → resources/views/campaigns/ (keep)

Outputs:
✓ social_route_mapping.md (38 routes → new canonical)
✓ social_controller_matrix.md (80 methods → 5 controllers)
✓ social_view_relocation.md
```

---

### BUNDLE C: Execution Wiring (Days 6–8)
**Deliverables:** Sentinel creation, signal registry, automation rebinding

```
New Files:
✓ System/Sentinels/SocialMediaSentinel.php
✓ System/Governance/SocialMediaGateway.php
✓ database/migrations/2026_04_xx_create_signal_spine.php
✓ System/Signals/SocialSignalRegistry.php

Sentinel Methods:
  draftPost($campaign, $postData) → ProcessRecord
  schedulePost($post, $schedule) → ProcessRecord
  publishPost($post) → PublishEvent
  recordOutcome($event, $metrics) → AnalyticsOutcome

Signal Registry:
  post.drafted
  post.scheduled
  post.executing
  post.published
  post.failed (escalate to admin)
  metrics.updated
  campaign.created
  campaign.started
  campaign.paused
  campaign.completed

Automation Listeners:
  RepeatScheduler (on post.published → next repeat)
  AnalyticsPoller (on post.published → fetch metrics in 24h, 7d)
  EscalationDispatcher (on post.failed → notify admin)

Outputs:
✓ social_sentinel_spec.md
✓ social_signal_catalog.md
✓ social_automation_rebind.md
✓ social_process_touchpoints.md
```

---

### BUNDLE D: Governance Layer (Days 9–10)
**Deliverables:** AEGIS checkpoints, escalation paths, audit hooks

```
Governance Checkpoints:
  permission_gate()
    ↳ user owns campaign?
  
  content_gate()
    ↳ post content meets platform requirements?
  
  automation_gate()
    ↳ prevent direct publish (must route via schedule)
  
  platform_gate()
    ↳ platform credentials valid?
  
  escalation_gate()
    ↳ if platform API fails → escalate to admin + notify user
  
  audit_gate()
    ↳ log all transitions (drafted→scheduled, published, failed)

Outputs:
✓ social_governance_matrix.md (checkpoint × scenario)
✓ social_escalation_paths.md (failure → escalation flow)
✓ social_audit_hook_map.md
```

---

### BUNDLE E: Database & Docs (Days 11–14)
**Deliverables:** Schema migration, data port, docs sync, drift audit

```
Migration Strategy:
  Phase 1: Create new schema (non-destructive)
    ├─ posts (from scheduled_posts, normalized)
    ├─ publish_events (new execution table)
    ├─ analytics_outcomes (new engagement table)
    ├─ post_schedules (extracted from ScheduledPost.repeat_*)
    ├─ social_accounts (extracted from automation_platforms)
    └─ social_campaigns (keep, rename if needed)
  
  Phase 2: Migrate data (dual-write for 2 weeks)
    ├─ scheduled_posts.* → posts.*
    ├─ (empty) → publish_events.* (backfill from user_post_job logs if available)
    ├─ (empty) → analytics_outcomes.* (backfill from external API)
    └─ automation_platforms.* → social_accounts.*
  
  Phase 3: Sunset old tables
    ├─ scheduled_posts (read-only archive, deprecate)
    ├─ automations (consolidate into config)
    └─ automation_platforms (replaced by social_accounts)

Model Changes:
  ScheduledPost → Post
  (none) → PublishEvent
  (none) → AnalyticsOutcome
  (none) → PostSchedule
  AutomationPlatform → SocialAccount

Outputs:
✓ social_migration_plan.md
✓ social_migration_audit.md (validation queries)
✓ social_drift_report.md (orphaned code, legacy refs)
✓ social_docs_manifest.md (all docs updated)
✓ social_rollback_plan.md (revert if needed)
```

---

## CRITICAL PATH

```
Week 1:
  Mon–Tue: Bundle A (Classification)
  Wed–Fri: Bundle B (Routes/Controllers)

Week 2:
  Mon–Wed: Bundle C (Sentinels/Signals)
  Thu–Fri: Bundle D (Governance)

Week 3:
  Mon–Wed: Bundle E (Database migration)
  Thu–Fri: Integration testing + rollback drills

Week 4:
  Mon–Fri: Production deployment + monitoring
```

---

## RISK CHECKLIST

- [ ] **ScheduledPost refactor:** Use dual-write strategy; keep old table 2 weeks
- [ ] **Platform services:** Abstract behind Sentinel facade; test OAuth still works
- [ ] **UI wizard:** Preserve step logic; update route/controller references only
- [ ] **AI integration:** Keep GenerateContentController; move service boundary
- [ ] **Data migration:** Validate all records port correctly; run reconciliation
- [ ] **Signal wiring:** Test each signal fires at correct lifecycle transition
- [ ] **Governance gates:** Test permission denial + escalation flow
- [ ] **Rollback:** Keep checkpoint snapshots after each bundle

---

## SUCCESS METRICS

| Metric | Target | Validation |
|--------|--------|------------|
| Entity lock coverage | 100% | social_entity_lock.md complete |
| Route canonicalization | 100% | 38 routes → canonical social.* |
| Sentinel authority | 100% | All domain logic flows through Sentinel |
| Signal emission | 100% | All 8 signals fire on schedule |
| Governance gates | 80% | 5/6 checkpoints active |
| ProcessRecord audit | 100% | All post transitions logged |
| Data migration | 100% | Row counts match; no orphans |
| Drift detection | <5 items | Drift report < 10 legacy refs |

---

## COMMAND CHECKLIST (Copilot Execution)

### Pass 1: Bundle A Classifier
```
Prompt: "Classify all Social Mode entities, controllers, routes, views. 
Produce entity_lock, terminology_registry, ambiguous_terms.
Input: AiSocialMedia/ full tree."
```

### Pass 2: Bundle B Canonicalizer
```
Prompt: "Rename routes automation.* → social.*. 
Align controllers to 5 functions. 
Relocate views. 
Produce route_mapping, controller_matrix, view_relocations."
```

### Pass 3: Bundle C Signal Linker
```
Prompt: "Build SocialMediaSentinel. 
Create 8 signals: post.drafted, post.scheduled, post.published, post.failed, metrics.updated, campaign.created, campaign.started, campaign.completed.
Wire ScheduledPostService to emit signals.
Produce sentinel_spec, signal_catalog, automation_rebind."
```

### Pass 4: Bundle D Governance Injector
```
Prompt: "Attach AEGIS checkpoints to SocialMediaGateway.
Define 5 gates: permission, content, automation, platform, escalation.
Produce governance_matrix, escalation_paths, audit_hooks."
```

### Pass 5: Bundle E Migration & Audit
```
Prompt: "Create new schema (posts, publish_events, analytics_outcomes, post_schedules).
Migrate data from scheduled_posts.
Audit for orphaned code and legacy refs.
Produce migration_plan, drift_report, docs_manifest."
```

---

## OUTPUTS TREE (All 45+ Deliverables)

```
docs/social/
├─ Phase1/
│  ├─ social_entity_lock.md
│  ├─ social_terminology_registry.md
│  └─ ambiguous_terms.md
├─ Phase2/
│  ├─ social_route_mapping.md
│  ├─ social_controller_matrix.md
│  └─ social_view_relocation.md
├─ Phase3/
│  ├─ social_sentinel_spec.md
│  ├─ social_signal_catalog.md
│  ├─ social_automation_rebind.md
│  └─ social_process_touchpoints.md
├─ Phase4/
│  ├─ social_governance_matrix.md
│  ├─ social_escalation_paths.md
│  └─ social_audit_hooks.md
├─ Phase5/
│  ├─ social_migration_plan.md
│  ├─ social_migration_audit.md
│  ├─ social_drift_report.md
│  ├─ social_docs_manifest.md
│  └─ social_rollback_plan.md
├─ New Code (Phase 3-5)
│  ├─ System/Sentinels/SocialMediaSentinel.php
│  ├─ System/Governance/SocialMediaGateway.php
│  ├─ System/Signals/SocialSignalRegistry.php
│  ├─ System/Http/Controllers/Social/
│  │  ├─ CampaignController.php
│  │  ├─ PostController.php
│  │  ├─ AccountController.php
│  │  ├─ AnalyticsController.php
│  │  └─ WorkflowController.php
│  └─ database/migrations/
│     ├─ 2026_04_xx_create_posts_table.php
│     ├─ 2026_04_xx_create_publish_events_table.php
│     ├─ 2026_04_xx_create_analytics_outcomes_table.php
│     └─ 2026_04_xx_create_post_schedules_table.php
└─ Execution Logs
   ├─ bundleA_execution.md
   ├─ bundleB_execution.md
   ├─ bundleC_execution.md
   ├─ bundleD_execution.md
   └─ bundleE_execution.md
```

---

**Next Step:** Queue Bundle A (Structural Classification) for Copilot execution. Estimated completion: April 4, 2026.
