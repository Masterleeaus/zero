# Business Agent Backend Scan (Social Suite → Business Agent)

## Scope scanned
- controllers, models, providers, routes
- requests/validators, jobs/commands, listeners
- menu service + dashboards/analytics widgets
- wizard flows, notifications, chat handlers, automation hooks

## Classification
- **Controllers (SocialMedia, AiSocialMedia, SocialMediaAgent):** still social semantics (posts/campaigns/platform/account). UI labels were adjusted in a prior pass but backend intents remain publishing-focused.
- **Models (ScheduledPost, AutomationCampaign, AutomationPlatform, Automation, SocialMedia* models):** still social semantics; store post/campaign fields and platform IDs with no lifecycle draft mapping.
- **Providers (SocialMedia, AiSocialMedia, SocialMediaAgent):** host-integrated for routes/config/translations; view loading now normalized to the business-suite theme. No lifecycle translation layer yet.
- **Routes (`routes/core/social.routes.php`):** route names preserved for compatibility; endpoints still map to post/campaign/calendar behaviors.
- **Requests/validators:** no dedicated FormRequest classes; controllers perform inline validation tied to post/campaign fields (still social semantics).
- **Jobs/commands (`GeneratePost*`, `UserPostJob`):** automation-capable but tied to post publishing; no signal hooks or lifecycle events yet.
- **Listeners:** none registered for social-suite events; no bridging to Titan Signal/Pulse.
- **Menu service:** UI-only renamed to Business Suite/Service Ops labels; backend targets remain legacy social controllers.
- **Dashboards/analytics:** still social metrics (posts/engagement/platform performance); no lifecycle outcome mapping.
- **Wizard flows (`AutomationStepController`, `AutomationController`):** still social semantics; schedules posts and campaigns.
- **Notifications:** none located for the suite; no lifecycle alerts.
- **Chat handlers (`SocialMediaAgentChatController` et al.):** still social/post semantics; no business agent intents.
- **Automation hooks:** scheduler wires generate-post commands only; no Titan Signal/Pulse seams.

## Immediate needs (follow-up refactor targets)
- Replace post/campaign/platform intent with work-draft lifecycle semantics (booking/quote/job/invoice/report).
- Add lifecycle-aware requests/DTOs and translation adapters without renaming tables yet.
- Introduce signal/automation events for draft lifecycle and agent review.
- Bridge data flows to host CRM (contacts/customers/locations), Work (bookings/jobs/checklists), and Money (quotes/invoices/payments).
- Re-map analytics to lifecycle outcomes and expose grouping hooks.

## Deferred items
- Calendar, analytics, and chat prompts still reference publishing; require lifecycle rewrite.
- No compatibility alias routes for future `business-suite.*`/`draft.*` yet (compatibility to be planned after backend rewrite).
