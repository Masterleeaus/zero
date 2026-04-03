# SOCIAL MEDIA MODULE × NEXUS ENGINE
## Structural Analysis & Realignment Blueprint

**Date:** April 2, 2026  
**Status:** Current-state assessment + Nexus compliance mapping  
**Objective:** Identify gaps, preservation requirements, and realignment steps

---

## I. CURRENT STATE SNAPSHOT

### Module Identity
- **Name:** AiSocialMedia (v4.5.0)
- **Type:** MagicAI v10 Extension
- **Namespace:** `App\Extensions\AISocialMedia\System`
- **Status:** Functionally complete; structurally pre-Nexus

### Entity Inventory

#### Core Models
| Model | Purpose | Relationships | Status |
|-------|---------|---------------|--------|
| `ScheduledPost` | Content scheduling record | belongs_to(AutomationPlatform), belongs_to(User) | ✓ Active |
| `Automation` | Automation config (JSON) | Generic key-value storage | ⚠ Legacy pattern |
| `AutomationPlatform` | Social platform identity | has_many(ScheduledPost) | ✓ Active |
| `AutomationCampaign` | Campaign metadata | Minimal relationships | ✓ Active |
| `TwitterSettings` | Platform credentials | Scope-specific | ✓ Active |
| `LinkedinTokens` | OAuth tokens | Scope-specific | ✓ Active |

#### Supporting Tables
- `automation_platforms` — Platform registry (name, settings, active)
- `scheduled_posts` — 18 columns, complex (content, media, repeat logic)
- `automation_campaigns` — Lightweight campaign container
- `twitter_settings` — Credentials storage
- `linkedin_tokens` — OAuth state

---

## II. NEXUS ENGINE REQUIREMENTS vs. CURRENT STATE

### A. Five-Mode Model Compliance

| Requirement | Current State | Gap | Priority |
|-----------|--------------|-----|----------|
| **Mode:** Social Media Mode | ✓ Defined in Nexus | N/A | CRITICAL |
| **Entity Grammar Lock** | ⚠ Implicit only | No explicit lock table | HIGH |
| **Shared Lifecycle (Process→Schedule→Event→Outcome→Signal→Audit)** | ✗ Partial only | No Process, missing Signal backbone | CRITICAL |
| **Mode Decider Routing** | ✗ None | Must add classifier | HIGH |
| **Sentinel Layer (Domain Authority)** | ✗ Distributed in controller | Need single Sentinel authority | CRITICAL |

### B. Agent Hierarchy Alignment

#### Current Architecture (Pre-Nexus)
```
Controllers (mixed authority)
  ├─ AutomationController (orchestration + domain logic)
  ├─ AutomationStepController (workflow UI)
  ├─ AutomationPlatformController (platform mgmt)
  └─ GenerateContentController (AI calls + automation)
      ↓
Services (scattered responsibilities)
  ├─ AutomationService
  ├─ ScheduledPostService
  ├─ InstagramService / TwitterService / LinkedInService
  └─ [No Sentinel abstraction]
      ↓
Database Models
```

#### Nexus Required Architecture
```
Envoy Layer (UI/Intent)
  ├─ AutomationStepController (interface presenter)
  └─ UploadController (asset intake)
      ↓
Core Layer (Orchestration)
  ├─ Mode Decider (social mode → Sentinel route)
  └─ ProcessRecord Factory (draft→execution pathway)
      ↓
Sentinel Layer (Domain Authority) ← MISSING
  └─ SocialMediaSentinel
      ├─ Campaign validation
      ├─ Post lifecycle governance
      ├─ Platform capability matching
      └─ Signal emission (post.created, post.scheduled, post.published)
          ↓
Scout Layer (Automation)
  └─ AutomationService (via Sentinel permission only)
      ├─ Content generation
      ├─ Platform publishing
      └─ Repeat scheduling
          ↓
AEGIS Governance Layer
  ├─ permission_gate: user → campaign ownership
  ├─ automation_gate: prevent direct publish (must be scheduled)
  ├─ escalation_gate: escalate failed platform publishes
  └─ audit_gate: track all post lifecycle transitions
```

---

## III. ENTITY MAPPING TO NEXUS GRAMMAR

### Social Media Mode Entity Lock (Per Nexus DOC 03 & 84)

| Nexus Universal | Social Media Concrete | Current Table | Nexus Status |
|-----------------|----------------------|---------------|--------------|
| **Draft** | Post | scheduled_posts | ✓ Exists |
| **Program** | Campaign | automation_campaigns | ✓ Exists |
| **Contact Profile** | Account | (implicit in automation_platforms) | ⚠ Needs table |
| **Channel Context** | Platform | automation_platforms | ✓ Exists |
| **Schedule** | Schedule (repeat_* fields) | scheduled_posts.repeat_* | ⚠ Denormalized |
| **Execution Event** | PublishEvent | ✗ Missing | CRITICAL |
| **Outcome Record** | AnalyticsRecord | ✗ Missing | CRITICAL |
| **Service Scope** | AudienceFilter | ✗ Missing (campaign_target only) | HIGH |
| **SOP Template** | Template | ✗ Missing | MEDIUM |

### Universal Lifecycle Instrumentation

| Nexus Stage | Social Media Equivalent | Current Implementation | Gap |
|------------|------------------------|----------------------|-----|
| **Process** | Campaign created | ✗ None | CRITICAL |
| **Schedule** | Post scheduled | ⚠ Partial (time fields only) | HIGH |
| **Event** | Platform publishes post | ✗ No execution event model | CRITICAL |
| **Outcome** | Engagement metrics | ✗ No outcome table | CRITICAL |
| **Signal** | post.published, post.failed | ✗ No signal spine | CRITICAL |
| **Audit** | Change history | ✗ No audit table | HIGH |

---

## IV. CRITICAL GAPS & PRESERVATION REQUIREMENTS

### PRESERVE (Intact in Nexus)
1. ✓ **Platform-specific services** (InstagramService, TwitterService, LinkedInService)
   - Handling OAuth, credential storage, API calls
   - Move to SocialMediaSentinel helpers

2. ✓ **Scheduled post content lifecycle** (topics → generation → scheduling)
   - Integrate with Schedule entity
   - Emit lifecycle signals at each transition

3. ✓ **Campaign automation workflows** (multi-step wizard)
   - Map to AutomationStepController → Envoy Layer

4. ✓ **AI content generation hooks** (GenerateContentController)
   - Integrate with CreatiCore (Claude Sonnet 4.5 routing)

### REDESIGN (Must align to Nexus)
1. ✗ **Automation model** — Generic key-value pattern is anti-pattern
   - Replace with proper Configuration entity scoped to mode/platform
   - Split into: PlatformConfiguration, AutomationRule, ScheduleTemplate

2. ✗ **ScheduledPost table** — 18 columns, many composite responsibilities
   - Split: Post (content draft) | PublishEvent (execution) | AnalyticsOutcome (results)
   - Add ProcessRecord instrumentation (proposed → scheduled → published)

3. ✗ **Missing Schedule entity** — Repeat logic embedded in ScheduledPost
   - Extract to standalone `schedule` table per Nexus pattern
   - Support: one-time, daily, weekly, monthly, custom recurrence

4. ✗ **Missing Signal infrastructure** — No event emission
   - Create Signal registry for Social Mode:
     - `campaign.created`
     - `post.drafted`
     - `post.scheduled`
     - `post.published`
     - `post.failed`
     - `metrics.updated`
   - Attach ScheduledPostService to emit on each transition

5. ✗ **Missing AEGIS governance** — No approval gates, escalation
   - Add permission checks: can user create campaigns? publish immediately or schedule-only?
   - Add escalation: if platform API fails, escalate to admin

6. ✗ **Missing ProcessRecord** — No approval pipeline
   - Post draft → (approval needed?) → scheduled → execution

---

## V. REALIGNMENT BLUEPRINT (PHASED)

### PHASE 1: Foundation (Entity Registry Lock)

**Objective:** Freeze Social Mode entity grammar and prepare Nexus infrastructure

#### 1A. Create SocialMediaEntityRegistry
```
schema/SocialMediaEntityRegistry.md
├─ Platform (channel identity: X, LinkedIn, Instagram)
├─ Account (org account on platform)
├─ Campaign (marketing initiative)
├─ Post (content draft)
├─ Schedule (posting cadence)
├─ PublishEvent (execution record)
├─ AnalyticsOutcome (engagement data)
└─ AudienceTarget (scope definition)
```

#### 1B. Create Signal Registry
```
database/migrations/
└─ 2026_04_02_xxxx_create_social_signals_table.php
    ├─ signal_name (enum: campaign.created, post.scheduled, etc.)
    ├─ entity_type (campaign, post, platform)
    ├─ entity_id
    ├─ tenant_id
    ├─ emitted_by (SocialMediaSentinel)
    ├─ payload (JSON)
    └─ created_at
```

#### 1C. Create ProcessRecord Instrumentation
```
tables to migrate:
├─ scheduled_posts (rename → posts, restructure)
├─ publish_events (new: execution records)
├─ analytics_outcomes (new: engagement results)
└─ post_approval_records (new: governance trail)
```

#### 1D. Update Routes & Controller Namespacing
```
Current:
  dashboard.user.automation.*
  
Nexus Target:
  dashboard.user.social.*
    ├─ campaign.* (manage campaigns)
    ├─ post.* (manage posts)
    ├─ account.* (manage platform accounts)
    └─ analytics.* (view outcomes)
```

---

### PHASE 2: Core Sentinel & Governance (Domain Authority)

**Objective:** Build SocialMediaSentinel as single source of Social Mode authority

#### 2A. Create SocialMediaSentinel
```php
App/Extensions/AISocialMedia/System/Sentinels/SocialMediaSentinel.php

public function draftPost(Campaign $campaign, array $postData): ProcessRecord
  → validate campaign ownership
  → validate audience target
  → create post (draft state)
  → emit post.drafted signal
  → return ProcessRecord

public function schedulePost(Post $post, Schedule $schedule): ProcessRecord
  → validate post content
  → validate schedule cadence
  → attach schedule to post
  → emit post.scheduled signal
  → return ProcessRecord with automation hook

public function publishPost(Post $post): PublishEvent
  → validate platform credentials
  → call platform service
  → create PublishEvent record
  → emit post.published signal
  → return execution result

public function recordOutcome(PublishEvent $event, array $metrics): AnalyticsOutcome
  → validate event exists
  → store metrics
  → emit metrics.updated signal
  → return outcome record
```

#### 2B. Create SocialMediaGateway (AEGIS Hooks)
```php
App/Extensions/AISocialMedia/System/Governance/SocialMediaGateway.php

Checkpoints:
├─ permission_gate()
│  └─ user can create campaigns for company?
├─ campaign_validity_gate()
│  └─ campaign targets valid audience?
├─ content_gate()
│  └─ post content meets platform requirements?
├─ automation_gate()
│  └─ prevent direct publish; must route through schedule
├─ escalation_gate()
│  └─ if platform publish fails → escalate to admin
└─ audit_gate()
   └─ attach audit event to every transition
```

#### 2C. Restructure AutomationService
```php
// BEFORE (mixed responsibilities)
AutomationService::createScheduledPost($data) // does too much

// AFTER (Sentinel coordinates)
AutomationService::generateContent($topics, $tone) // AI only
AutomationService::publishViaInstagram($token, $post) // platform only
AutomationService::publishViaTwitter($auth, $post) // platform only

// All lifecycle controlled via SocialMediaSentinel
SocialMediaSentinel::scheduleAutomatedPost(...)
```

---

### PHASE 3: Signal & Lifecycle Integration

**Objective:** Wire all post/campaign transitions through Signal infrastructure

#### 3A. Instrument Post Lifecycle
```
Post State Machine:

drafted
  ↓ (approve or schedule)
scheduled
  ↓ (time reached)
executing
  ↓ (platform API call)
published / failed
  ↓ (poll analytics)
metrics_collected
  ↓
archived

Signals:
- post.drafted → post.scheduled
  emit: post.scheduled (post_id, schedule_id, scheduled_for)

- post.scheduled → post.executing
  emit: post.executing (post_id, platform, attempt)

- post.executing → post.published
  emit: post.published (post_id, platform, platform_id, published_at)

- post.executing → post.failed
  emit: post.failed (post_id, platform, error_code, error_msg)
  escalate: admin notification

- post.published → metrics_collected
  emit: metrics.updated (post_id, likes, shares, comments, etc.)
```

#### 3B. Instrument Campaign Lifecycle
```
Campaign State Machine:

created
  ↓
posts_added
  ↓
automation_enabled
  ↓
running / paused
  ↓
completed / archived

Signals:
- campaign.created → campaign.configured
  emit: campaign.created (campaign_id, owner_id, name)

- campaign.configured → campaign.active
  emit: campaign.started (campaign_id)

- campaign.active → campaign.paused
  emit: campaign.paused (campaign_id, reason)

- campaign.completed
  emit: campaign.completed (campaign_id, total_posts, total_reach)
```

#### 3C. Wire Signals to Subscribers
```
Signal Subscribers (Automation Engines):
├─ RepeatScheduler
│  └─ on post.published → schedule next recurrence
├─ AnalyticsPoller
│  └─ on post.published → schedule metrics pull (24h, 7d)
├─ EscalationDispatcher
│  └─ on post.failed → notify admin
└─ NotificationBroadcaster
   └─ on post.published → user notification
```

---

### PHASE 4: Database Refactor

#### 4A. Migration Strategy
```
Step 1: Create new schema
  ├─ posts (from scheduled_posts, normalized)
  ├─ publish_events (execution records)
  ├─ analytics_outcomes (engagement data)
  ├─ post_schedules (repeat rules)
  ├─ social_campaigns (campaign container)
  └─ social_accounts (platform accounts)

Step 2: Migrate data
  scheduled_posts.* → posts.*, publish_events.*, post_schedules.*

Step 3: Update relationships
  Models → new structure
  Controllers → new relationships

Step 4: Deprecate old tables
  scheduled_posts (keep for rollback)
  automations (consolidate into configuration)
```

#### 4B. Schema Outlines

```sql
-- Posts Table
CREATE TABLE posts (
    id BIGINT PRIMARY KEY,
    campaign_id BIGINT NOT NULL,
    company_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    -- Content
    topic VARCHAR(255),
    prompt TEXT,
    content TEXT,
    media JSON,
    tone VARCHAR(50),
    length VARCHAR(50),
    visual_format VARCHAR(50),
    visual_ratio VARCHAR(20),
    -- State
    status ENUM('drafted', 'scheduled', 'published', 'failed'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES social_campaigns(id),
    INDEX (campaign_id, status, created_at)
);

-- PublishEvents Table
CREATE TABLE publish_events (
    id BIGINT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    platform VARCHAR(50) NOT NULL, -- instagram, twitter, linkedin
    platform_post_id VARCHAR(255),
    attempted_at TIMESTAMP,
    published_at TIMESTAMP,
    status ENUM('pending', 'published', 'failed'),
    error_code VARCHAR(50),
    error_msg TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    INDEX (post_id, platform, published_at)
);

-- AnalyticsOutcomes Table
CREATE TABLE analytics_outcomes (
    id BIGINT PRIMARY KEY,
    publish_event_id BIGINT NOT NULL,
    impressions INT,
    engagement INT,
    likes INT,
    shares INT,
    comments INT,
    clicks INT,
    saves INT,
    poll_date DATE,
    created_at TIMESTAMP,
    FOREIGN KEY (publish_event_id) REFERENCES publish_events(id),
    INDEX (publish_event_id, poll_date)
);

-- PostSchedules Table
CREATE TABLE post_schedules (
    id BIGINT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    schedule_type ENUM('once', 'daily', 'weekly', 'monthly'),
    start_date DATE,
    end_date DATE,
    time_of_day TIME,
    days_of_week JSON, -- for weekly
    day_of_month INT, -- for monthly
    next_run DATETIME,
    is_active BOOLEAN,
    created_at TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    INDEX (is_active, next_run)
);
```

---

### PHASE 5: UI/Routes Realignment

#### 5A. Route Restructuring
```
BEFORE (legacy):
  dashboard.user.automation.index
  dashboard.user.automation.step.*
  dashboard.user.automation.scheduled-posts
  dashboard.user.automation.platform.*
  dashboard.user.automation.campaign.*

AFTER (Nexus social):
  dashboard.user.social.posts.index
  dashboard.user.social.posts.create
  dashboard.user.social.posts.edit
  dashboard.user.social.posts.delete
  
  dashboard.user.social.campaigns.index
  dashboard.user.social.campaigns.create
  dashboard.user.social.campaigns.edit
  
  dashboard.user.social.accounts.index
  dashboard.user.social.accounts.connect
  dashboard.user.social.accounts.disconnect
  
  dashboard.user.social.analytics.campaign
  dashboard.user.social.analytics.post
```

#### 5B. Controller Realignment
```
BEFORE:
  App\Extensions\AISocialMedia\System\Http\Controllers\
  ├─ AutomationController (70+ methods, mixed concerns)
  ├─ AutomationStepController (wizard state)
  ├─ GenerateContentController (AI)
  ├─ AutomationPlatformController
  └─ UploadController

AFTER (Nexus Social):
  App\Extensions\AISocialMedia\System\Http\Controllers\Social\
  ├─ CampaignController (CRUD campaigns)
  ├─ PostController (CRUD posts)
  ├─ AccountController (manage platform accounts)
  ├─ AnalyticsController (view engagement)
  ├─ ContentGeneratorController (AI integration)
  └─ WorkflowController (step-by-step automation wizard)
```

---

## VI. IMPLEMENTATION CHECKLIST

### Bundle A: Structural Classification
- [ ] Classify all controllers, routes, views to Social Mode
- [ ] Lock entity registry (Platform, Account, Campaign, Post, etc.)
- [ ] Identify ambiguous terms (automation → automation rule? scheduling engine?)
- [ ] **Output:** `social_entity_lock.md`, `social_terminology_registry.md`

### Bundle B: Naming Canonicalization
- [ ] Rename routes: `dashboard.user.automation.*` → `dashboard.user.social.*`
- [ ] Rename controllers: `AutomationController` → `{Campaign,Post,Account}Controller`
- [ ] Rename views: automation-steps → workflow-steps
- [ ] Remove duplicate automation code
- [ ] **Output:** `social_route_mapping.md`, `social_controller_matrix.md`

### Bundle C: Execution Wiring
- [ ] Create SocialMediaSentinel
- [ ] Attach ProcessRecord pipeline to post lifecycle
- [ ] Create Signal registry (post.drafted, post.scheduled, post.published, metrics.updated)
- [ ] Instrument ScheduledPostService to emit signals
- [ ] Create automation listeners (RepeatScheduler, AnalyticsPoller)
- [ ] **Output:** `social_signal_catalog.md`, `social_process_touchpoints.md`

### Bundle D: Governance Layer
- [ ] Create SocialMediaGateway with AEGIS checkpoints
- [ ] Add permission_gate: user campaign ownership
- [ ] Add automation_gate: enforce schedule-only publish
- [ ] Add escalation_gate: failed platform publishes
- [ ] Add audit_gate: all lifecycle transitions
- [ ] **Output:** `social_governance_matrix.md`, `social_escalation_paths.md`

### Bundle E: Database & Docs
- [ ] Create new schema: posts, publish_events, analytics_outcomes, post_schedules
- [ ] Migrate data from scheduled_posts
- [ ] Sync models and relationships
- [ ] Update documentation
- [ ] Audit for drift (legacy Automation table, orphaned code)
- [ ] **Output:** `social_migration_audit.md`, `social_drift_report.md`, `social_docs_manifest.md`

---

## VII. RISK MITIGATION

### High-Risk Areas
1. **ScheduledPost refactor** — 18 columns, 10+ active features
   - Mitigation: Dual-write strategy during migration; keep old table read-only for 2 weeks
   
2. **Platform service dependencies** — Direct OAuth, API calls scattered
   - Mitigation: Wrap in Service Facade; switch behind Sentinel gradually
   
3. **UI wizard state** — Multi-step automation complex
   - Mitigation: Map step-by-step to WorkflowController; preserve state machine
   
4. **AI content generation** — Tightly coupled to controller
   - Mitigation: Extract to ContentGeneratorService (keep implementation, move responsibility)

### Testing Checkpoints
- [ ] Unit: Signal emission for each lifecycle transition
- [ ] Integration: Post draft → schedule → publish → metrics
- [ ] E2E: User creates campaign → schedules 5 posts → metrics collected
- [ ] Governance: Unapproved user cannot publish immediately
- [ ] Escalation: Platform API failure triggers admin notification

---

## VIII. NEXUS COMPLIANCE SCORECARD

| Criterion | Before | After | Delta |
|-----------|--------|-------|-------|
| **Entity Grammar Lock** | 0% | 100% | ✓✓✓ |
| **Sentinel Authority** | Distributed | Centralized | ✓✓✓ |
| **Lifecycle Instrumentation** | 40% (partial) | 100% | ✓✓✓ |
| **Signal Emission** | 0% | 100% | ✓✓✓ |
| **AEGIS Governance** | 0% | 80% (configurable) | ✓✓✓ |
| **ProcessRecord** | None | Audit trail | ✓✓✓ |
| **Route Canonicalization** | Mixed | Unified social.* | ✓✓ |
| **View Ownership** | Scattered | Organized by function | ✓✓ |
| **Code Organization** | 60% (needs refactor) | 95% | ✓✓ |

---

## IX. EXECUTION SEQUENCE

1. **Week 1:** Bundle A (Classification) + Bundle B (Routes/Controllers)
2. **Week 2:** Bundle C (Signals) + Bundle D (Governance)
3. **Week 3:** Bundle E (Database migration) + Full integration testing
4. **Week 4:** Rollback readiness + Production deployment

---

## CONCLUSION

The Social Media module is **functionally robust** but **architecturally pre-Nexus**. It requires systematic realignment to:

1. ✓ Establish SocialMediaSentinel as single source of domain authority
2. ✓ Lock entity grammar (Platform → Account → Campaign → Post → PublishEvent → Outcome)
3. ✓ Instrument post lifecycle with signals (drafted → scheduled → published → metrics)
4. ✓ Attach AEGIS governance gates (permission, automation, escalation, audit)
5. ✓ Normalize database schema (split ScheduledPost into Post + PublishEvent + AnalyticsOutcome + Schedule)

**Preservation:** Campaign automation wizard, platform-specific services, AI content generation remain largely intact; integration improves and centralizes.

**Timeline:** 3–4 weeks to full Nexus compliance with rolling deployment.
