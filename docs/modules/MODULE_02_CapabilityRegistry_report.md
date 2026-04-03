# MODULE 02 — CapabilityRegistry: Technician Skill Graph Engine

**Status:** Installed  
**Installed at:** 2026-04-03  
**Signal tags:** `capability.skill_assigned`, `capability.cert_expired`, `capability.gap_detected`

---

## Overview

CapabilityRegistry is a structured, versioned, graph-backed skill and certification tracking system for technicians. It models skill levels, expiry, endorsements, certifications, compliance requirements, and live availability windows.

It feeds directly into TitanDispatch (Module 01) as the primary data source for `evaluateSkillMatch()`.

---

## Artifacts Delivered

### Migration

| File | Tables Created |
|------|---------------|
| `2026_04_03_800100_create_capability_registry_tables.php` | `skill_definitions`, `technician_skills`, `certifications`, `skill_requirements`, `availability_windows`, `availability_overrides` |

### Models (`app/Models/Team/`)

| Model | Table | Key Notes |
|-------|-------|-----------|
| `SkillDefinition` | `skill_definitions` | BelongsToCompany; `requires_certification`, `expiry_months`, `is_active` |
| `TechnicianSkill` | `technician_skills` | Ordinal `meetsLevel()`, `isExpired()`; `active()` scope filters expiry |
| `Certification` | `certifications` | BelongsToCompany; hard gate on `expires_at`; `active()` / `expiringSoon()` scopes |
| `SkillRequirement` | `skill_requirements` | Links `JobType` ↔ `SkillDefinition` with `minimum_level` and `is_mandatory` |
| `AvailabilityWindow` | `availability_windows` | Recurring day-of-week windows; `active()` + `forDay()` scopes |
| `AvailabilityOverride` | `availability_overrides` | Date-specific overrides; unique per `(user_id, date)` |

### User Model — new relationships

```php
$user->technicianSkills()       // HasMany → TechnicianSkill
$user->certifications()         // HasMany → Certification (no global company scope)
$user->availabilityWindows()    // HasMany → AvailabilityWindow
$user->availabilityOverrides()  // HasMany → AvailabilityOverride
```

### JobType Model — new relationship

```php
$jobType->skillRequirements()   // HasMany → SkillRequirement
```

### Services

| Service | Location | Key Methods |
|---------|----------|-------------|
| `CapabilityRegistryService` | `app/Services/Team/` | `getSkillProfile()`, `hasSkill()`, `hasCertification()`, `getCertificationStatus()`, `getExpiringCertifications()`, `isAvailable()`, `matchJobRequirements()` |
| `SkillComplianceService` | `app/Services/Team/` | `getComplianceGaps()`, `generateComplianceReport()` |

### Events (`app/Events/Team/`)

| Event | Payload |
|-------|---------|
| `SkillAssigned` | `TechnicianSkill $technicianSkill` |
| `CertificationExpired` | `Certification $certification` |
| `CertificationRevoked` | `Certification $certification` |
| `CapabilityGapDetected` | `User $user`, `?JobType $jobType`, `array $missing`, `array $expired` |

### Listeners (`app/Listeners/Team/`)

| Listener | Handles | Action |
|----------|---------|--------|
| `NotifyOnCertificationExpiry` | `CertificationExpired` | Logs cert expiry; stub for notification wiring |
| `RecordCapabilityAuditTrail` | All 4 Team events | Records to AuditTrail under `capability_registry` process |

### Controller

`app/Http/Controllers/Team/CapabilityController`

| Method | Route | Notes |
|--------|-------|-------|
| `profile()` | `GET /dashboard/team/capabilities/profile` | Placeholder view |
| `skills()` | `GET /dashboard/team/capabilities/skills` | JSON |
| `certifications()` | `GET /dashboard/team/capabilities/certifications` | JSON |
| `availability()` | `GET /dashboard/team/capabilities/availability` | JSON: windows + overrides |
| `gaps()` | `GET /dashboard/team/capabilities/gaps` | JSON compliance report |

Routes registered under `dashboard.team.capabilities.*` in `routes/core/team.routes.php`.

### DispatchConstraintService Integration

`app/Services/Work/DispatchConstraintService::evaluateSkillMatch()` now uses `CapabilityRegistryService::matchJobRequirements()` when available:

- `missing` or `expired` skills → returns `0.0`
- All requirements matched → returns `1.0`
- No registry / no requirements → falls back to legacy `0.7`

### Tests

| File | Type | Coverage |
|------|------|---------|
| `tests/Unit/Services/Team/CapabilityRegistryServiceTest.php` | Unit | `hasSkill`, `hasCertification`, `getCertificationStatus`, `isAvailable`, ordinal helpers |
| `tests/Feature/Team/CapabilityControllerTest.php` | Feature | Route registration, skills/certifications/availability/gaps endpoints |

---

## Architecture Notes

- **Skill levels are ordinal:** `trainee < competent < proficient < expert`
- **Expired certifications are a hard gate:** `hasCertification()` returns `false` regardless of stored status if `expires_at` is past
- **Availability:** overrides take precedence over recurring windows
- **Compliance indexes:** `(company_id, expires_at)`, `(user_id, expires_at)`, `(user_id, skill_definition_id)` all optimised

---

## Integration Map

| Connects to | Detail |
|-------------|--------|
| `users` | User model extended with 4 HasMany relationships |
| `job_types` | JobType extended with `skillRequirements()` |
| `DispatchConstraintService` | `evaluateSkillMatch()` now delegates to CapabilityRegistryService |
| `SignalDispatcher` / `AuditTrail` | `RecordCapabilityAuditTrail` listener writes to `capability_registry` process |
| `EventServiceProvider` | 4 events registered with 2 listeners |
| `routes/core/team.routes.php` | 5 routes under `dashboard.team.capabilities.*` |
