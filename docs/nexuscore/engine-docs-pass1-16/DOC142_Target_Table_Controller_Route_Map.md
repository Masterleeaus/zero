# Target Table / Controller / Route Map

## Jobs Mode
Tables:
- tz_core_jobs
- tz_core_sites
- tz_core_checklists
Controllers:
- Jobs\JobController
- Jobs\ChecklistController
Routes:
- titan.jobs.*

## Comms Mode
Tables:
- tz_core_conversations
- tz_core_messages
- tz_core_channels
Controllers:
- Comms\ConversationController
- Comms\MessageController
Routes:
- titan.comms.*

## Finance Mode
Tables:
- tz_core_invoices
- tz_core_payments
- tz_core_adjustments
Controllers:
- Finance\InvoiceController
- Finance\PaymentController
Routes:
- titan.finance.*

## Admin Mode
Tables:
- tz_core_roles
- tz_core_permissions
- tz_core_extensions
Controllers:
- Admin\RoleController
- Admin\PermissionController
Routes:
- titan.admin.*

## Social Mode
Tables:
- tz_core_campaigns
- tz_core_posts
- tz_core_platforms
Controllers:
- Social\CampaignController
- Social\PostController
Routes:
- titan.social.*
