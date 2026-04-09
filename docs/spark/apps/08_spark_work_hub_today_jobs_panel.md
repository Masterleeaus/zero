# [spark] Work Hub Today Jobs Panel

## 1. Purpose
Build the mobile-first Today panel for Work Hub showing today's jobs, alerts, and next actions. This is the daily operational heartbeat view for field staff and dispatchers.

## 2. Why this app is priority
It is the day-to-day operational heartbeat for staff and dispatchers. This panel will be opened the most often of any Work Hub surface and must be fast and reliable.

## 3. Mobile-first UX requirements
- Single-column job cards
- Sticky today summary strip
- Large action buttons
- Fast loading and clear offline-aware states

## 4. Chat-launch / wizard-launch behavior
- Launch from Nexus Home or direct hub zoom
- Chat should be able to open filtered views such as "show late jobs" or "show unassigned work"

## 5. Data boundaries and persistence rules
- List and filter jobs via core endpoints
- UI may cache for responsiveness, but truth remains in core job and lifecycle structures

## 6. Docs to read first
- Nexus bundle: `DOC80_Jobs_Mode_Blueprint.md`
- Nexus bundle: `DOC18_View_Surface_Map.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/MOBILE_STACK_ALIGNMENT.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md`

## 7. Drift reconciliation note
Translate older Jobs Mode references into Work Hub terminology. "Jobs Mode today view" = "Work Hub Today Panel". Job status chips should use current lifecycle states, not old mode-specific enums.

## 8. Scope included
- Today summary strip
- Job list cards
- Status chips
- Quick actions to open setup/checklist/completion flows
- Empty/loading/error states

## 9. Scope excluded
- Full drag-and-drop dispatch board
- Advanced route optimization
- Payroll or invoice analytics

## 10. Acceptance criteria
- [ ] Mobile-first job list is usable as standalone panel
- [ ] Supports filtered states: late / unassigned / at risk
- [ ] Quick actions deep-link into related wizards
- [ ] Ready to embed inside future Work Hub shell

## 11. Suggested component breakdown
- `TodaySummaryStrip`
- `JobCardList`
- `JobCard`
- `FilterChips`
- `QuickActionBar`

## 12. Suggested API/data dependencies
- today jobs endpoint
- job status summary endpoint
- action launch map

## 13. Output expectation for Spark
Produce a standalone React mini-app that can serve as the Work Hub Today surface on phone-sized screens.

---
**Labels:** `spark`, `nexus`, `work-hub`, `mobile`, `field-worker`
