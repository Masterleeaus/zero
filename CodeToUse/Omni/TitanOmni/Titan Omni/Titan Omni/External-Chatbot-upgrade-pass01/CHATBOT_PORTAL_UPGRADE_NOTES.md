# Chatbot Portal Upgrade Notes

This pass upgrades the Chatbot extension toward a cleaning-business customer portal using a reuse-first WorkCore strategy.

## Reused WorkCore tables
- companies / company_addresses
- users / client_details / client_contacts
- sites
- quotes / quotations / quotation_items / estimate_requests
- invoices / invoice_items / invoice_files / payments
- tickets / ticket_replies
- projects / project_milestones / project_time_logs / task_notes

## New extension bridge tables added
- ext_chatbot_portal_site_profiles
- ext_chatbot_portal_recurring_services
- ext_chatbot_portal_feedback
- ext_chatbot_portal_notifications

## New chatbot config fields
- is_customer_portal
- portal_home_title
- portal_primary_cta
- portal_modules
- portal_quick_actions
- portal_settings

## New conversation link fields
- site_id
- workcore_project_id
- workcore_invoice_id
- workcore_ticket_id
- portal_context

## New API endpoints
- GET /api/v2/chatbot/{chatbot}/session/{sessionId}/portal
- GET /api/v2/chatbot/{chatbot}/session/{sessionId}/portal/notifications

## What this pass enables
- portal mode flag on chatbots
- portal home config delivery in API resource
- customer/site memory records
- recurring service records
- feedback and re-clean records
- customer notification feed
- WorkCore object links from conversations

## What still needs wiring
- admin UI toggles for new portal fields
- frontend widget Home tab rendering from portal payload
- WorkCore invoice / site / project lookups beyond simple counts
- booking actions and invoice payment actions
- support issue creation from portal quick actions
