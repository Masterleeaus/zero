# Copilot Task: Consolidate BelongsToCompany Traits

## Context
This is a Laravel 10 multi-tenant SaaS app. There are two `BelongsToCompany` traits:

- `app/Models/Concerns/BelongsToCompany.php` — full version with global scope auto-filtering by `company_id` (PREFERRED)
- `app/Traits/BelongsToCompany.php` — lightweight version, only `company()` relation + `scopeForCompany()`, NO global scope

The `User` model intentionally uses the lightweight `App\Traits\BelongsToCompany` (global scope on User would break auth).
All other models should use `App\Models\Concerns\BelongsToCompany`.

## Current State
These models were already migrated: `Team`, `TeamMember`, `UserOpenaiChat`, `Folders`, `UserOpenai`.
`User` stays with `App\Traits\BelongsToCompany` — do NOT change it.

## Your Task
1. Run: `grep -rn "App\\Traits\\BelongsToCompany" app/` to find any remaining models still using the old trait
2. For each one (except `User`): change `use App\Traits\BelongsToCompany;` → `use App\Models\Concerns\BelongsToCompany;`
3. Verify each migrated model has `company_id` in `$fillable` (required for the boot method to auto-set it)
4. Run `php artisan config:clear && php artisan route:clear && php artisan cache:clear`
5. Run `php artisan tinker --execute="App\Models\Team\Team::count();"` to confirm no errors

## Files to Check
- `app/Models/` directory recursively
- Do NOT touch `app/Models/User.php`
- Do NOT delete `app/Traits/BelongsToCompany.php` (User still needs it)
