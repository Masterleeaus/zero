# TITAN OMNI — PASS 08: Campaign Runtime + Sequences

**Labels:** `titan-omni` `pass-08` `campaigns` `sequences` `automation` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 07 complete

---

## Global Instruction

Re-read all prior pass outputs before building. Reuse donor campaign logic wherever safe. Runtime links to host objects — it does not own them. Support manual stop/start and full inspection.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Build Omni's automation and follow-up runtime — sequences, templates, and campaign execution engine.

---

## Read First — Docs

- `docs/titan-omni/campaign-runtime/TITAN_OMNI_CAMPAIGN_RUNTIME.md`
- `docs/titan-omni/models/TITAN_OMNI_MODEL_MAP.md`
- `docs/titan-omni/services/TITAN_OMNI_SERVICE_BINDINGS.md`
- `docs/titan-omni/integration-surface/TITAN_OMNI_INTEGRATION_SURFACE.md`
- `docs/titan-omni/channel-drivers/TITAN_OMNI_OUTBOUND_IMPLEMENTATION_REPORT.md` (Pass 07 output)

---

## Read First — CodeToUse

- `CodeToUse/MarketingBot*` — sequence/campaign/template definitions
- `CodeToUse/MarketingBot_Integrated_v4_CommsUpgrades*` (if present) — upgraded campaign logic
- `CodeToUse/utilities*` — scheduling/delayed execution utilities
- Any donor sequence runner, template engine, or drip campaign code

---

## Build

### Sequence Runtime Engine
- `app/Omni/Services/Campaign/SequenceRunnerService.php`
  - `start(Sequence $sequence, Contact $contact, ?Model $hostObject = null): SequenceRun`
  - `advance(SequenceRun $run): void` — process next due step
  - `evaluateDelay(SequenceStep $step, SequenceRun $run): Carbon` — when to send next
  - `stop(SequenceRun $run, string $reason): void`
  - `pause(SequenceRun $run): void`
  - `resume(SequenceRun $run): void`

### Sequence Step Dispatcher
- `app/Omni/Services/Campaign/StepDispatcherService.php`
  - `dispatch(SequenceStep $step, SequenceRun $run): void`
  - Resolves correct channel via driver registry
  - Renders template for step
  - Calls `OutboundSendService::send()`
  - Records step result on `SequenceRun`

### Template Engine
- `app/Omni/Services/Campaign/TemplateRenderService.php`
  - `render(string $templateBody, array $variables): string`
  - Variable sources: Contact, linked Customer, linked Job, linked Quote, linked Invoice
  - Support for channel-specific rendering (SMS = short, Email = HTML)

### Scheduled Job
- `app/Jobs/Omni/ProcessDueSequenceSteps.php` — runs every minute via scheduler
  - Finds all `SequenceRun` records with `next_step_due_at <= now()`
  - Dispatches `SequenceRunnerService::advance()` for each

### Built-in Sequence Flows

Implement as seeded/default `Sequence` + `SequenceStep` records:

1. **Lead Nurture** — new lead → 3-touch follow-up over 7 days
2. **Quote Reminder** — sent quote + no response → reminder at day 3, day 7
3. **Invoice Reminder** — overdue invoice → reminder chain
4. **Rebooking Follow-up** — job completed → ask for rebook at 30/60/90 days
5. **Feedback Recovery** — negative feedback signal → human escalation trigger

### Admin UI (basic)
- `resources/views/default/panel/user/omni/sequences/index.blade.php` — list sequences
- `resources/views/default/panel/user/omni/sequences/show.blade.php` — sequence detail + active runs
- `app/Http/Controllers/Omni/SequenceController.php`
  - `index()`, `show()`, `start()`, `stop()`, `pause()`, `resume()`

---

## Rules

- Sequence runtime links to host objects (Job, Quote, Invoice, Customer) — it does NOT own them
- Every step dispatch must create a `Delivery` record (via Pass 07 `OutboundSendService`)
- Stop conditions must be checked before each step: reply received, opt-out, host object status changed
- Reuse donor campaign logic from `MarketingBot*` wherever safe — do not rewrite if reusable
- Template rendering must be safe against missing variables (null coalescing, not exceptions)

---

## Required Output

- Services in `app/Omni/Services/Campaign/`
- Jobs in `app/Jobs/Omni/`
- Views in `resources/views/default/panel/user/omni/sequences/`
- Controllers in `app/Http/Controllers/Omni/`
- `docs/titan-omni/campaign-runtime/TITAN_OMNI_SEQUENCE_IMPLEMENTATION_REPORT.md`

---

## Pass Delivery Rules

1. What was scanned
2. What donor campaign code was reused
3. What host code was extended
4. What files were created
5. What docs were updated
6. What remains for Pass 09
