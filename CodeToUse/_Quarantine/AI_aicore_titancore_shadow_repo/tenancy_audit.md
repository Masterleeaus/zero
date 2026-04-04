## Tenancy Audit Report

### Boundary updates
- Added `tenant()` helper to standardize company tenancy resolution (falls back to legacy `team_id` when `company_id` is missing).
- Introduced `BelongsToCompany` trait with `scopeForCompany`.
- Added `company_id` columns (nullable, indexed) to key tenant tables (`users`, `user_openai`, `user_openai_chat`, `folders`, `teams`, `team_members`) with backfill from existing `team_id` where available.
- Models now capture `company_id` on create (Users, Teams, TeamMembers, Folders, UserOpenai, UserOpenaiChat).
- AI controllers/services now stamp `company_id` on new generation records; dashboard queries include company scoping.

### Remaining team_id usages (crew grouping / pending conversion)
- Controllers: Payment/PlanAndPricing, TTS, AIChat, AdvancedImage, API AI image/chat flows still reference `team_id` for routing and visibility.
- Services: StreamService still sets `team_id` in several update paths; company stamping now present for creation but further filtering may be required.
- Extensions/Generators: OpenAi/GeneratorController and TeamController continue to rely on `team_id` for crew management and invitations.
- Config: Spatie permission config keeps `team_id` key (teams mode disabled) — currently for compatibility.

### Legacy compatibility
- Backfill uses `team_id` where present to seed `company_id` for existing records.
- Trait scopes and helper fallback allow existing team-only data to remain accessible while transitioning to company boundaries.

### Tenant-safe confirmations
- Dashboard document loading (`UserDashboardService`, `Dashboard\UserController`) now enforces `company_id` filtering alongside team grouping for shared folders/docs.
- AI generation APIs/controllers stamp `company_id` on creation to align new records with company tenancy.
