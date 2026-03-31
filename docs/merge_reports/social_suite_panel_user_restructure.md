# Social Suite Panel User Restructure

## View moves
- **Old base**: `resources/views/default/social-media` and `resources/views/default/social-media-agent`
- **New base**: `resources/views/default/panel/user/business-suite/social-media` and `resources/views/default/panel/user/business-suite/social-media-agent`
- Providers now load views from the new panel user business-suite paths.

## Routes & controllers
- Existing panel routes remain (`routes/core/social.routes.php` with `dashboard/user/social-media*` prefixes).
- Controllers unchanged; view namespace resolution updated via provider paths.

## Menu changes
- Re-labelled menu group to **AI Business Suite** with operations-focused entries:
  - Command Center, Programs, Contacts, Work Drafts, Master Schedule.
- Agent dropdown renamed to **Service Ops Agents** with items: Command Center, Agents, Archived Drafts, Master Schedule, Insights, Client Accounts.
- Admin settings menu label updated to **Business Suite Settings**.

## Label changes (operations model)
- Dashboard/title language updated to “AI Business Suite” / “Command Center.”
- Draft creation CTA now surfaces Booking, Quote, Service Job, Invoice, Report options.
- Campaigns → Programs; Platforms/Accounts → Contacts/Client Accounts; Posts → Work Drafts; Calendar → Master Schedule; Analytics → Insights.
- Agent UI copy shifted from “Social Media Agent” to “Service Ops Agent.”

## Domain bridges
- UI labels now align with existing host CRM/Money/Work terminology (contacts, jobs, quotes, invoices, schedule, insights) without duplicating domain models.

## Deferred / follow-ups
- Backend draft-type handling still routes to existing post creation endpoint (passes `draft_type` query for future use).
- Further deep copy updates (inner form field wording) can be handled in a dedicated pass if required.
