# [spark] Site Memory Panel

## 1. Purpose
Build the panel for persistent site memory: access instructions, hazards, preferences, restrictions, and recurring context. Site memory is surfaced in field workflows and improves repeat-service quality.

## 2. Why this app is priority
Site memory is a strong product differentiator and improves repeat-service quality immediately. It is shared between Work Hub and Office Hub and reused across every job at a given site.

## 3. Mobile-first UX requirements
- Read-first layout for in-field use
- Edit controls available but secondary
- Sections for access, hazards, preferences, restrictions, recurring notes
- Fast scan on small screens

## 4. Chat-launch / wizard-launch behavior
- Launch from Work Hub, Today Jobs Panel, Job Setup, or chat intents like "show site notes"
- Can be opened in read mode (field use) or edit mode (admin/office use) depending on context

## 5. Data boundaries and persistence rules
- Persist site memory to scoped operational memory structures linked to tenant/site/job context
- Keep local cache secondary to core truth

## 6. Docs to read first
- Nexus bundle: `DOC80_Jobs_Mode_Blueprint.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md`
- Nexus bundle: `DOC83_Admin_Mode_Blueprint.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md`

## 7. Drift reconciliation note
Treat site memory as Work/Office shared operational memory, not as a generic notes widget or old-mode artifact. It belongs jointly to Work Hub (field read) and Office Hub (admin edit). Do not conflate it with CRM contact notes or Omni conversation history.

## 8. Scope included
- Access instructions section
- Hazards / restrictions section
- Customer preferences section
- Recurring service notes section
- Edit/read mode toggle

## 9. Scope excluded
- Full CRM contact history
- Dispatch board
- Invoice/payment logic

## 10. Acceptance criteria
- [ ] Site memory renders clearly on mobile
- [ ] Supports read and edit modes
- [ ] Can be launched from Work flows and chat
- [ ] Data model assumptions respect tenancy and operational memory boundaries

## 11. Suggested component breakdown
- `SiteMemoryHeader`
- `AccessSection`
- `HazardSection`
- `PreferencesSection`
- `RecurringNotesSection`
- `EditToggleBar`

## 12. Suggested API/data dependencies
- site memory read endpoint
- site memory update endpoint
- site metadata summary endpoint

## 13. Output expectation for Spark
Produce a standalone React mini-app for site memory that is mobile-first, field-friendly, and composable inside Work and Office contexts.

---
**Labels:** `spark`, `nexus`, `work-hub`, `office-hub`, `mobile`, `field-worker`
