# TitanBOS — Convert Business Suite Extensions from Social Media to Service Operations

## Strategic Context

This app (Titan Zero, a Laravel 10 SaaS) contains three extensions originally built
for social media management:
- `business-suite` (formerly `social-media`) — posts, campaigns, calendar, platforms
- `business-suite-agent` (formerly `social-media-agent`) — agent wizard, chat, analytics
- `ai_business_suite` (formerly `ai_social_media`) — AI generation, automation hooks

These extensions are being **converted**, not deleted. Their UI shell, scheduling
infrastructure, wizard flows, agent orchestration, and dashboard surfaces are
valuable and will be repurposed into a **service-business operations system**.

The doctrine is: **social-media architecture kept, social-media meaning removed.**

The extensions must stop representing content publishing and start representing:
- work drafts (quotes, bookings, service jobs, invoices, reports)
- programs/workflows (recurring service plans)
- master schedules (job/booking/follow-up timelines)
- client contacts (not social platforms/accounts)
- business agents (operational AI assistants, not posting bots)
- operational insights (conversion rates, throughput — not vanity metrics)

---

## Current File Locations

- Menu entries: `app/Services/Common/MenuService.php` — keys `ext_business_suite_*`
- Extension views (if published): check under `resources/views/` for any
  `business-suite` or `social-media` subfolder paths
- Host panel views: `resources/views/default/panel/user/business-suite/`
- Routes: registered by extension package under `dashboard.user.social-media.*`
  (the extension still registers as `social-media` from the marketplace —
  our MenuService keys and labels are renamed but route values stay as `social-media`)
- Model: `app/Models/BusinessSuiteAccount.php` (table: `business_suite_accounts`)
- Plan field: `business_suite_agent_limits` on `plans` table
- Tests: `tests/Feature/BusinessSuite/`, `tests/Feature/BusinessSuiteAgent/`

---

## CRITICAL — Extension Compatibility Rule

The social media extensions install from the MagicAI marketplace and register their
own routes as `dashboard.user.social-media.*` and their own key as `social-media`.

**To keep the extensions installable AND have TitanBOS branding, the rule is:**

| What | Keep as | Do NOT change to |
|---|---|---|
| Menu entry PHP keys | `ext_business_suite_*` | stay |
| Menu `'label'` strings | TitanBOS vocabulary | ✓ change |
| Menu `'route'` values | `dashboard.user.social-media.*` | do NOT change to business-suite |
| `Route::has(...)` checks | `dashboard.user.social-media.*` | do NOT change |
| `MarketplaceHelper::isRegistered(...)` | `'social-media'` / `'social-media-agent'` | do NOT change |
| Host views folder | `business-suite/` | ✓ keep |
| Host views content/labels | TitanBOS vocabulary | ✓ change |

**The installed extension provides routes + logic. The host code provides
renamed menu entries + custom TitanBOS-branded wrapper views.**

Example of correct MenuService entry after this task:
```php
'ext_business_suite_dropdown' => [
    'key'              => 'ext_business_suite_dropdown',   // ← renamed key ✓
    'route'            => 'dashboard.user.social-media.index', // ← keep extension route
    'label'            => 'TitanBOS',                     // ← renamed label ✓
    'active_condition' => ['dashboard.user.social-media.*'],   // ← keep extension routes
    'show_condition'   => Route::has('dashboard.user.social-media.index')
                          && MarketplaceHelper::isRegistered('social-media'), // ← keep
],
```

---

## Pass 1 — View Relocation (already started, verify and complete)

**Goal:** All business-suite views must live under the host panel structure
`resources/views/default/panel/user/business-suite/` — not as detached
extension views in a separate package namespace.

Tasks:
1. Confirm `resources/views/default/panel/user/business-suite/` exists with `index.blade.php`
2. If any extension views exist elsewhere (e.g. published vendor views), copy them
   into this folder under the renamed structure
3. Create subfolders: `drafts/`, `programs/`, `contacts/`, `schedule/`, `insights/`
4. Each subfolder needs at minimum: `index.blade.php`, `show.blade.php`, `form.blade.php`
5. All views must extend `default.panel.layout.app` and use the host Tailwind/Alpine stack

---

## Pass 2 — User-Facing Relabeling (menu labels + view text)

**Goal:** Zero social-media vocabulary visible to end users.

### MenuService changes (`app/Services/Common/MenuService.php`)

**Only change `'label'` strings and menu entry PHP array keys.**
**DO NOT change `'route'`, `Route::has()`, or `MarketplaceHelper::isRegistered()` values.**
Do NOT touch `marketing_bot_*`, `blogpilot_*`, `creative_suite`, or any other extension.

Restore any `'route'` / `Route::has()` / `isRegistered()` values that were previously
changed from `social-media` to `business-suite` — they must point back to:
- routes: `dashboard.user.social-media.*`
- isRegistered: `'social-media'` and `'social-media-agent'`

| Menu key | Current label | New label |
|---|---|---|
| `ext_business_suite_dropdown` | Business Suite | TitanBOS |
| `ext_business_suite` | Dashboard | Command Center |
| `ext_business_suite_campaign` | Campaigns | Programs |
| `ext_business_suite_platform` | Platforms | Contacts |
| `ext_business_suite_post` | Drafts | Work Drafts |
| `ext_business_suite_calendar` | Calendar | Master Schedule |
| `ext_business_suite_agent_dropdown` | Titan Agent | Business Agent |
| `ext_business_suite_agent_dashboard` | Dashboard | Agent Dashboard |
| `ext_business_suite_agent_agents` | Agents | My Agents |
| `ext_business_suite_agent_archived_posts` | Archived Drafts | Completed Items |
| `ext_business_suite_agent_calendar` | Calendar | Agent Schedule |
| `ext_business_suite_agent_analytics` | Analytics | Performance |
| `ext_business_suite_agent_accounts` | Contacts | Client Accounts |
| `ext_business_suite_agent_chat` | Chat | Agent Chat |
| `ai_business_suite_extension` (admin) | TitanBOS | TitanBOS |
| `business_suite_agent_chat_settings` (admin) | Social Media Agent Chat | Business Agent Chat |
| `business_suite_accounts` (admin) | Business Suite Accounts | TitanBOS Accounts |

### Introduction enum (`app/Enums/Introduction.php`)

```php
case BUSINESS_SUITE = 'business_suite';
// label string:
self::BUSINESS_SUITE => __('TitanBOS'),
```

### In-view text replacements (apply to all views under business-suite/)

| Old text | New text |
|---|---|
| Generate New Post | Create Work Draft |
| New Post | New Draft |
| Social Media Post | Work Draft |
| All Posts | All Drafts |
| Published Posts | Finalized Items |
| Scheduled Posts | Scheduled Items |
| Platform | Contact / Client |
| Campaign | Program |
| Connect Account | Add Client Contact |
| Audience | Customer Group |
| Total Posts | Total Drafts |
| Published | Finalized |
| Facebook / Instagram / X (tabs) | Bookings / Quotes / Jobs / Invoices |
| Post engagement | Work item status |
| Analytics | Insights |
| Calendar | Master Schedule |

---

## Pass 3 — Draft-Type UI Conversion

**Goal:** Replace "new post" creation flow with a "new work draft" selection flow.

### In `business-suite/drafts/` or equivalent create view:

1. Replace single text/media creation form with a **draft type selector**:
   ```blade
   <select name="draft_type">
       <option value="booking">Booking</option>
       <option value="quote">Quote</option>
       <option value="service_job">Service Job</option>
       <option value="invoice">Invoice</option>
       <option value="report">Report</option>
   </select>
   ```

2. Below the selector, show a dynamic form section per draft type.
   Each draft type should pre-populate fields relevant to that object.

3. A draft stays in "Work Drafts" status until user approves it.
   On approval it transitions to the relevant domain
   (e.g. Quote → `App\Models\Money\Quote`, Booking → `App\Models\Work\Site`, etc.)

4. The "Generate" / AI-assist button label becomes "AI Draft" and calls the
   existing AI generation hook with a business-operations system prompt context.

### Draft model bridge

Create `app/Services/BusinessSuite/DraftService.php`:

```php
namespace App\Services\BusinessSuite;

class DraftService
{
    /**
     * Promote an approved draft into its host domain record.
     * Maps draft_type to the appropriate model + factory method.
     */
    public function promote(array $draft, int $companyId, int $userId): mixed
    {
        return match ($draft['draft_type']) {
            'quote'       => $this->promoteToQuote($draft, $companyId, $userId),
            'booking'     => $this->promoteToBooking($draft, $companyId, $userId),
            'service_job' => $this->promoteToServiceJob($draft, $companyId, $userId),
            'invoice'     => $this->promoteToInvoice($draft, $companyId, $userId),
            'report'      => $this->promoteToReport($draft, $companyId, $userId),
            default       => throw new \InvalidArgumentException("Unknown draft type: {$draft['draft_type']}"),
        };
    }

    private function promoteToQuote(array $draft, int $companyId, int $userId): \App\Models\Money\Quote
    {
        return \App\Models\Money\Quote::create([
            'company_id'  => $companyId,
            'created_by'  => $userId,
            'customer_id' => $draft['contact_id'] ?? null,
            'title'       => $draft['title'],
            'status'      => 'draft',
            'currency'    => $draft['currency'] ?? 'AUD',
        ]);
    }

    private function promoteToBooking(array $draft, int $companyId, int $userId): \App\Models\Work\Site
    {
        return \App\Models\Work\Site::create([
            'company_id'  => $companyId,
            'created_by'  => $userId,
            'customer_id' => $draft['contact_id'] ?? null,
            'name'        => $draft['title'],
            'status'      => 'pending',
            'scheduled_at' => $draft['scheduled_at'] ?? null,
        ]);
    }

    private function promoteToServiceJob(array $draft, int $companyId, int $userId): \App\Models\Work\ServiceJob
    {
        return \App\Models\Work\ServiceJob::create([
            'company_id'  => $companyId,
            'created_by'  => $userId,
            'title'       => $draft['title'],
            'status'      => 'pending',
        ]);
    }

    private function promoteToInvoice(array $draft, int $companyId, int $userId): \App\Models\Money\Invoice
    {
        return \App\Models\Money\Invoice::create([
            'company_id'  => $companyId,
            'created_by'  => $userId,
            'customer_id' => $draft['contact_id'] ?? null,
            'title'       => $draft['title'],
            'status'      => 'draft',
            'currency'    => $draft['currency'] ?? 'AUD',
        ]);
    }

    private function promoteToReport(array $draft, int $companyId, int $userId): array
    {
        // Reports are not yet a dedicated model — return draft payload for now
        return array_merge($draft, ['promoted_at' => now()->toISOString()]);
    }
}
```

---

## Pass 4 — Programs (Campaign → Recurring Service Plan)

**Goal:** Reinterpret campaigns as recurring service programs.

In the campaigns/programs views, update:

1. "Campaign name" → "Program name"
2. "Campaign objective" → "Program type" (options: Recurring Clean, Follow-Up Sequence, Quote Reminder, Invoice Reminder, End-of-Lease Workflow)
3. "Audience" → "Client Group" or "Target Contacts"
4. "Post frequency" → "Service cadence" (Weekly / Fortnightly / Monthly / Custom)
5. "Content plan" → "Work Draft template"
6. Remove any social engagement metrics (likes, shares, reach) from program analytics

---

## Pass 5 — Contacts (Platform/Account → Client Contact)

**Goal:** Reinterpret social media accounts/platforms as client contacts.

In the platforms/accounts views, update:

1. Page title: "Platforms" → "Contacts"
2. "Connect Account" button → "Add New Contact"
3. "Platform" field → "Client Name"
4. "Account Handle / Username" → "Contact Name"
5. "Active/Passive" badge → keep, relabel tooltip as "Active client" / "Passive lead"
6. Where platform icons show (Facebook/Instagram/X logos): replace with
   channel source icons — Phone, Web Portal, Email, Walk-In
7. "Followers / Reach" metrics → "Active Jobs" / "Outstanding Quotes"

Bridge to CRM: When a new Contact is added, optionally create/link a
`App\Models\Crm\Customer` record with `company_id` scoped correctly.

---

## Pass 6 — Master Schedule (Calendar → Lifecycle Timeline)

**Goal:** Publishing calendar becomes operational schedule.

In the calendar/schedule views:

1. Title: "Publishing Calendar" → "Master Schedule"
2. Event types on calendar:
   - Instead of post publish events → show: quote follow-up dates, booking times,
     job execution times, invoice reminders, program checkpoints
3. Calendar event create form: replace "Schedule Post" with "Add Schedule Item"
   with fields: item type (Quote Follow-Up / Booking / Job / Invoice Reminder),
   linked contact, date/time, notes

---

## Pass 7 — Business Agent Conversion

**Goal:** Agent wizard converts from social posting bot to operational agent builder.

### Agent type selector (new first step in wizard)

Add a new "Agent Type" selection step before all other wizard steps:

```
What kind of agent are you building?
○ Quote Follow-Up Agent — follows up on open quotes automatically
○ Booking Coordinator — helps schedule and confirm bookings
○ Job Prep Agent — sends job briefs and checklists before a service job
○ Invoice Follow-Up Agent — chases overdue invoices
○ Retention / Rebook Agent — proactively contacts clients for repeat bookings
○ Custom Operations Agent — define your own workflow
```

### Wizard step relabeling

| Old step label | New step label |
|---|---|
| Business Description | Agent Objective |
| Target Audience | Target Clients |
| Content Tone | Communication Style |
| Posting Schedule | Trigger Schedule |
| Content Plan | Workflow Plan |
| Platform Selection | Client Channel Selection |

### Agent dashboard metrics (replace social metrics)

| Old metric | New metric |
|---|---|
| Posts generated | Drafts created |
| Published | Actions taken |
| Engagement rate | Conversion rate |
| Follower growth | Client retention |
| Best posting time | Best contact time |

---

## Pass 8 — Operational Insights (Analytics → Business Performance)

**Goal:** Vanity social metrics replaced with operational KPIs.

Replace or relabel dashboard metric cards:

| Old card | New card |
|---|---|
| Total Posts | Total Drafts |
| Published Posts | Finalized Items |
| Impressions | Work Items Created |
| Engagement | Completion Rate |
| Best performing content | Most converted draft type |
| Audience growth | New contacts added |

Filter tabs in drafts list view:
- Remove: All / Published / Scheduled / Failed
- Replace with: All \| Bookings \| Quotes \| Jobs \| Invoices \| Reports

Source/channel column (previously platform column in post lists):
- Replace platform icons with channel icons: Phone, Web Portal, Email, Walk-In

---

## Pass 9 — Internal Rename (files, classes, routes, migrations)

**Goal:** No social-media naming left anywhere internally.

This pass is deferred until Passes 1–8 are stable. When ready, execute:

1. Any remaining `social_media_*` table columns → add migration to rename them
2. Any class names still containing `SocialMedia` → rename
3. Any SCSS classes `.lqd-social-media-post-item` → rename to `.lqd-draft-item`
4. Any JS variables/objects referencing `socialMedia` → rename to `businessSuite` or `draft`
5. Any route file references to `social-media` → already done via sed; verify none remain
6. SQL seed files in `resources/dev_tools/` referencing `social_media` → update seeder
7. Create `database/migrations/YYYY_MM_DD_rename_residual_social_media_columns.php`
   for any remaining column renames

---

## Pass 10 — Bridge to Host Domains

**Goal:** Business Suite acts as orchestration layer over CRM/Work/Money, not a parallel system.

Rules:
- Business Suite does NOT own its own Quote/Invoice/Booking database tables
- When a draft is "finalized/promoted", it creates a record in the relevant host domain:
  - Quote → `App\Models\Money\Quote`
  - Booking → `App\Models\Work\Site`
  - Service Job → `App\Models\Work\ServiceJob`
  - Invoice → `App\Models\Money\Invoice`
  - Contact → `App\Models\Crm\Customer`
- Business Suite reads back from host domains to display status/progress
- `DraftService::promote()` (created in Pass 3) handles this bridge

---

## Constraints — Do Not Break These

- Do NOT rename route keys (`dashboard.user.business-suite.*`) — already correct
- Do NOT rename PHP class keys or database column names mid-pass — do it in Pass 9
- Do NOT touch `marketing_bot_*`, `blogpilot_*`, `creative_suite`, or other
  extension menu entries in MenuService
- Do NOT touch `app/Http/Controllers/Api/SocialLoginController.php` — OAuth login
- Do NOT rebuild from scratch what can be reused — harvest the architecture
- All company_id scoping must be preserved on any new records created via promote()
- The `BusinessSuiteAccount` model (`business_suite_accounts` table) maps to Contacts —
  do NOT delete this model; it bridges to `App\Models\Crm\Customer`

---

## Summary

The three extensions (business-suite, business-suite-agent, ai_business_suite) are
being converted into a unified **TitanBOS** service-business operations layer with:

- **Command Center** — operational dashboard
- **Work Drafts** — AI-assisted work item creation (quotes/bookings/jobs/invoices)
- **Programs** — recurring service plans and workflow sequences
- **Contacts** — client targets (bridged to CRM customers)
- **Master Schedule** — lifecycle timeline for all operational events
- **Business Agent** — AI operational assistants (not posting bots)
- **Performance** — operational KPIs (not vanity social metrics)

Architecture rule: **reuse the shell, convert the semantics, bridge to host domains.**
