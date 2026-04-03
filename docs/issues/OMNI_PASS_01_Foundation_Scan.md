# TITAN OMNI — PASS 01: Foundation Scan + Ownership Freeze

**Labels:** `titan-omni` `pass-01` `architecture` `do-not-activate`
**Milestone:** Titan Omni Implementation

---

## Global Instruction

Deep-scan the repo first, then implement this pass fully.

You must use:
- `docs/` in repo root
- `CodeToUse/` in repo root
- the Omni docs already in the repo under `docs/titan-omni/`
- existing host code before creating anything new

Do not rebuild from scratch. Do not create a parallel comms system. Do not duplicate CRM, Jobs, Finance, or tenant ownership. Use `company_id` as the tenant boundary. Preserve audit trails, delivery history, identity evidence, and routing history.

**Always begin by re-reading:**
- `docs/titan-omni/`
- relevant `docs/` files outside `docs/titan-omni/`
- relevant `CodeToUse/` donor code
- existing host implementation in `app/`, `routes/`, `database/`, `resources/`, `config/`

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Lock the architecture, map ownership boundaries, and identify exactly where Omni fits in the host.

---

## Read First — Docs

- `docs/titan-omni/architecture/TITAN_OMNI_ARCHITECTURE.md`
- `docs/titan-omni/integration-surface/TITAN_OMNI_INTEGRATION_SURFACE.md`
- `docs/titan-omni/alignment/TITAN_OMNI_PASS_ALIGNMENT.md`
- `docs/titan-omni/alignment/TITAN_OMNI_ZERO_ALIGNMENT.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_SOURCE_INVENTORY.md`
- Any existing comms / omni / signal / routing docs in `docs/`

---

## Read First — CodeToUse

- `CodeToUse/MarketingBot*`
- `CodeToUse/comms*` (if present)
- `CodeToUse/TitanTalk*` (if present)
- `CodeToUse/extension_library*` (if present)
- `CodeToUse/utilities*`
- Any existing channel / messaging / webhook / campaign donor code

---

## Tasks

- Deep-map all comms-related existing code in host
- Find existing models, controllers, routes, migrations, views, service providers, jobs, events
- Create a hard ownership map:
  - What Omni owns
  - What host owns
  - What is linked only
- Identify exact insertion points in:
  - `app/`
  - `routes/`
  - `database/`
  - `resources/`
  - `config/`

**Do not build migrations yet. This pass is structural freeze and insertion planning only.**

---

## Required Output

Create the following files:

- `docs/titan-omni/source-maps/TITAN_OMNI_HOST_INSERTION_MAP.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_EXISTING_CODE_AUDIT.md`
- `docs/titan-omni/alignment/TITAN_OMNI_OWNERSHIP_FREEZE.md`

---

## Pass Delivery Rules

Output must include:
1. What was scanned
2. What donor code was found and assessed
3. What host code was identified as insertion points
4. What files were created/changed
5. What docs were updated
6. What remains for Pass 02

Do not output tiny placeholder passes. Build a substantial, complete scan.
