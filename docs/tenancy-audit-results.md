# Tenancy Audit Results

## Files changed
- `app/Http/Controllers/Dashboard/UserController.php`
  - Folder lookup now scopes by `company_id` and only applies `team_id` as an optional secondary filter when a team is present, preventing null-team cross-access.

## Patterns updated
- Replaced unconditional `team_id` filtering with primary `company_id` scoping plus optional `team_id` sub-filtering to align with the tenancy boundary.

## Edge cases retained as team-specific
- Team assignment and creation logic (e.g., team member listings and stream entry `team_id` assignments) remain unchanged because they are about team ownership, not data isolation.
