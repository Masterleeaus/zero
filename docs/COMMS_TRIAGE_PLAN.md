# Communications Module Triage (Phase 12)

The WorkCore communications features (KnowledgeBase/Playbooks, Notice Board, Team Chat) are not yet integrated. They remain **feature-flagged off** until they can be implemented and validated in MagicAI core.

## Current state
- No production controllers, models, routes, or views exist for KnowledgeBase, Notice Board, or Team Chat.
- Support routes include a TODO placeholder for migrating WorkCore support/communication endpoints.
- AI/Chatbot functionality is present separately and remains unaffected.

## Flags
The following flags are defined in `config/workcore.php` and default to `false`:
- `knowledgebase`
- `noticeboard`
- `teamchat`

## Next steps to enable
1) Implement scoped models, migrations, controllers, routes, and policies for each module following the tenant doctrine (`company_id` scoped; `team_id` for chat membership).  
2) Build dashboard views (panel layouts) with search, pagination, and real-time updates (team chat).  
3) Add request validation and authorization checks for staff-only access.  
4) Write feature tests covering CRUD, view counts (knowledge base), notice read tracking, and chat broadcasting.  
5) Flip the feature flags to `true` once validated in staging.
