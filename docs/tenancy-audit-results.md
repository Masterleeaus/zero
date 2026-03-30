# Tenancy Boundary Audit — `team_id` vs `company_id`

**Date:** 2026-03-30  
**Scope:** `app/Http/Controllers/` and `app/Services/`  
**Primary tenant boundary:** `company_id` (enforced via `BelongsToCompany` global scope)  
**Secondary boundary:** `team_id` (team-sharing feature, optional)

---

## 1. Search Results — `->where('team_id'` Data Filters

### Exact `->where('team_id', ...)` in controllers / services

| File | Line | Usage | Verdict |
|---|---|---|---|
| `app/Http/Controllers/Team/TeamController.php` | 33 | `->where('team_id', $team->getAttribute('id'))` | ✅ **OK** — listing members of a specific team (team-management, not tenancy) |
| `app/Models/Concerns/BelongsToTeam.php` | 20 | `scopeForTeam()` helper | ✅ **OK** — utility scope, not a data-access filter |

### `->orWhere('team_id', ...)` secondary filters

| File | Lines | `is_shared` checked? | Verdict |
|---|---|---|---|
| `app/Http/Controllers/OpenAi/GeneratorController.php` | 1128, 1131 | ✅ yes | ✅ OK — company scope via `BelongsToCompany`; team is secondary |
| `app/Http/Controllers/AIChatController.php` | 452, 455 | ✅ yes | ✅ OK |
| `app/Http/Controllers/Dashboard/UserController.php` (openai method) | 1103, 1106 | ✅ yes | ✅ OK |
| `app/Services/Dashboard/UserDashboardService.php` | 42, 45 | ✅ yes | ✅ OK |
| `app/Http/Controllers/Dashboard/UserController.php` (Folders lookup) | **784** | ❌ **no** | 🔴 **FIXED** — see §3 below |

---

## 2. `app/Services/Stream/StreamService.php` — Mixed Company/Team Logic

**Finding:** `StreamService` uses `team_id` exclusively for **record assignment** (setting `team_id` on new `UserOpenai` entries). It does **not** filter data by `team_id`. `company_id` is always set via `tenant()`.

Example pattern (repeated across ~6 `firstOrCreate` / `create` calls):

```php
$entry = UserOpenai::firstOrCreate(
    ['id' => $message_id],
    [
        'company_id' => tenant(),        // ← primary tenant key
        'team_id'    => $user->team_id,  // ← assignment only, not a filter
        ...
    ]
);
```

**Verdict:** ✅ No data-access filter issues in `StreamService.php`.

---

## 3. Bug Fixed — `UserController` Folder Lookup (line 784)

### Problem

The `documentsAll()` method looked up the current folder using a direct `team_id` comparison with no `is_shared` flag check:

```php
// BEFORE — inconsistent, no is_shared check
$currfolder = Folders::query()
    ->where('company_id', tenant())          // redundant — Folders uses BelongsToCompany
    ->where(function (Builder $query) {
        $query->where('created_by', auth()->id())
              ->orWhere('team_id', auth()->user()->team_id);  // ← bug: no is_shared
    })
    ->findOrFail($folderID);
```

Any user belonging to a team could access **all** folders tagged with that `team_id`, even when the team was not marked as shared — inconsistent with every other query in the same controller.

### Fix Applied

```php
// AFTER — consistent with openai() method pattern
$team        = $request->user()->getAttribute('team');
$myCreatedTeam = $request->user()->getAttribute('myCreatedTeam');

$currfolder = Folders::query()
    ->where(function (Builder $query) use ($team, $myCreatedTeam) {
        $query->where('created_by', auth()->id())
            ->when($team || $myCreatedTeam, function ($q) use ($team, $myCreatedTeam) {
                if ($team && $team?->is_shared) {
                    $q->orWhere('team_id', $team->id);
                }
                if ($myCreatedTeam) {
                    $q->orWhere('team_id', $myCreatedTeam->id);
                }
            });
    })
    ->findOrFail($folderID);
```

The redundant explicit `->where('company_id', tenant())` was also removed — `Folders` uses the `BelongsToCompany` global scope which already enforces company isolation.

---

## 4. Core Controllers — Redundant `->where('company_id', ...)` Audit

All models in the Core domain use the `BelongsToCompany` global scope (`App\Models\Concerns\BelongsToCompany`), which automatically injects `WHERE company_id = <auth user company>` into every query. Manual `->where('company_id', ...)` calls on these models are therefore redundant.

### Fixed in this audit

| Controller | Change |
|---|---|
| `InsightsController` (27 occurrences) | Removed all redundant `->where('company_id', $companyId)` from Eloquent queries on BelongsToCompany models. `$companyId` is retained only for explicit static-method calls (`Expense::totalForCompany`, `Leave::conflictsWithShifts`, `Attendance::statusSummary`) that require it as an argument. |
| `ExpenseCategoryController` (index) | Removed redundant `->where('company_id', ...)` from the listing query. |

### Remaining redundant wheres (not yet removed — defense-in-depth)

The following controllers still have redundant `->where('company_id', ...)` or `abort_if($model->company_id !== ...)` guards. The `abort_if` checks provide defense-in-depth authorization and are intentionally left in place. The `->where('company_id', ...)` guards are redundant but harmless; they can be removed in a follow-up cleanup.

| Controller | Occurrences | Notes |
|---|---|---|
| `ExpenseCategoryController` (`edit`, `update`, `destroy`) | 3 × `abort_if` | Authorization guards — keep |
| `ExpenseController` | 3 × `abort_if` + 3 × `where` | `where` redundant; `abort_if` keep |
| `InvoiceController` | 3 × `where` | Redundant |
| `QuoteController` | 4 × `where` | Redundant |
| `AttendanceController` | 2 × `where` + 2 × `abort_if` | `where` redundant; `abort_if` keep |
| `ChecklistController` | 1 × `where` | Redundant |
| `LeaveController` | 4 × `where` + 5 × `abort_if` | `where` redundant (except `User::query()` — User has no global scope); `abort_if` keep |
| `ServiceAgreementController` | 3 × `where` + 2 × `abort_if` | `where` redundant; `abort_if` keep |
| `ServiceJobController` | 5 × `where` | Redundant |
| `ShiftController` | 3 × `where` + 1 × `abort_if` | `where` redundant; `abort_if` keep |
| `TimelogController` | 1 × `where` + 2 × `abort_if` | `where` redundant; `abort_if` keep |

> **Note on `User::query()->where('company_id', ...)`:** The `User` model uses `App\Traits\BelongsToCompany` (a simpler trait without a global scope), so explicit `->where('company_id', ...)` on User queries is **not** redundant and must remain.

---

## 5. Summary

| Area | Finding | Status |
|---|---|---|
| `TeamController` — `->where('team_id', ...)` | Team-membership listing; not a tenancy filter | ✅ No change needed |
| `UserController` — Folders lookup | Missing `is_shared` check; inconsistent pattern | ✅ **Fixed** |
| `StreamService` | team_id for assignment only; company_id via `tenant()` | ✅ No change needed |
| `InsightsController` — 27 redundant wheres | Redundant; models use BelongsToCompany | ✅ **Removed** |
| `ExpenseCategoryController` — index | Redundant `where('company_id', ...)` | ✅ **Removed** |
| Other Core controllers — redundant wheres | Documented above; not removed | ⬜ Follow-up |
| `orWhere('team_id', ...)` with `is_shared` check | Correct pattern (company primary, team secondary) | ✅ No change needed |

### Correct Pattern (for reference)

```php
// RIGHT — company_id is the primary tenant boundary (via BelongsToCompany global scope)
// team_id is an optional secondary filter for shared-team content
ModelName::query()
    ->where(function ($query) use ($team, $myCreatedTeam) {
        $query->where('user_id', auth()->id())
            ->when($team?->is_shared, fn ($q) => $q->orWhere('team_id', $team->id))
            ->when($myCreatedTeam,    fn ($q) => $q->orWhere('team_id', $myCreatedTeam->id));
    });

// WRONG — team_id as primary filter, no company scope
->where('team_id', auth()->user()->team_id)
```
