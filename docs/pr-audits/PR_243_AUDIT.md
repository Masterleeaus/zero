# PR #243 — [WIP] Update HRM for Source Reconciliation and Domain Completion

**Status:** HOLD (Audit Pass 2 — 2026-04-04)  
**Risk Level:** Low (no code changes present)  
**Domain:** HRM

## 1. Purpose

HRM Pass 2: source reconciliation scan + domain completion for team structure, shift scheduling,
biometric ingestion hooks, employment lifecycle, leave approval flows, payroll input layer,
department hierarchy, org chart, and role metadata.

## 2. Scope

**Only commit:** `Initial plan` — zero file changes beyond the PR description.

## 3. Hold Reason

PR #243 contains only the initial plan commit. No code has been implemented.
There is nothing to review or merge.

## 4. Gap Analysis — What This Pass Should Implement

| Stage | Expected Deliverable | Status |
|---|---|---|
| STAGE 1 | `docs/HRM_SOURCE_RECONCILIATION.md` | ❌ Missing |
| STAGE 2 | `docs/HRM_DOMAIN_GAP_MATRIX.md` | ❌ Missing |
| STAGE 3 | Team structure: `team_memberships`, `departments`, `manager_id` chain | ❌ Missing |
| STAGE 4 | Shift management extension (rotating, recurring, location-aware) | ❌ Missing |
| STAGE 5 | `BiometricIngestService` | ❌ Missing |
| STAGE 6 | `PayrollInputService` | ❌ Missing |
| STAGE 7 | Employment lifecycle states on `staff_profiles` | ❌ Missing |
| STAGE 8 | `departments` table + `Department` model | ❌ Missing |
| STAGE 9 | Approval pipeline for leave/timesheets/shifts | ❌ Missing |
| STAGE 10 | HRM event bus extensions (5 new events) | ❌ Missing |
| STAGE 11 | Policy coverage (StaffProfilePolicy, DepartmentPolicy, etc.) | ❌ Missing |
| STAGE 12 | Test coverage for new HRM features | ❌ Missing |

## 5. Merge Decision

**HOLD** — No implementation commits. Requires a dedicated HRM Pass 2 execution.

## 6. Recommended Follow-up

Assign HRM Pass 2 as next dedicated agent task. The PR description is complete and correct —
the 12-stage implementation plan should be followed as specified.
