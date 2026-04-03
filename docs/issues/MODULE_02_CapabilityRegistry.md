# MODULE 02 — CapabilityRegistry: Technician Skill Graph Engine

**Label:** `titan-module` `capabilities` `skills` `team`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** High

---

## Overview

Build the **CapabilityRegistry** — a structured, versioned, graph-backed skill and certification tracking system for technicians. This goes far beyond a flat skills list: it models skill levels, expiry, endorsements, certifications, compliance requirements, and live availability windows.

CapabilityRegistry feeds directly into TitanDispatch (Module 01) as the primary data source for `evaluateSkillMatch()`. It also surfaces compliance gaps, drives training recommendations, and gates job assignment by certification validity.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Models/Team/` — all team/user models, understand existing user attributes
2. Read `app/Models/Work/JobType.php` — understand how job types define skill requirements
3. Read `app/Models/Work/ServiceJob.php` — fields that reference technician skill needs
4. Read `app/Titan/Signals/SignalDispatcher.php` and `AuditTrail.php` — signal emission pattern
5. Read `app/Services/Work/DispatchConstraintService.php` (once Module 01 is installed) — skill evaluation hook points
6. Read `database/migrations/` — all existing `users`, `teams`, `job_types` table structures
7. Read `docs/titancore/` — scan for any capability, certification, or compliance design docs
8. Read `docs/nexuscore/` — scan for skill graph, workforce, or technician profile docs
9. Read `app/Models/Concerns/BelongsToCompany.php` — multi-tenant trait pattern
10. Read `CodeToUse/work/` and `CodeToUse/managed-premises/` — scan for any skill/certification entity files

---

## Canonical Models to Extend / Reference

- User model (team) — primary entity being enriched
- `app/Models/Work/JobType.php` — requirement definition source
- `app/Models/Work/ServiceJob.php` — skill requirement consumer
- `app/Models/Concerns/BelongsToCompany.php` — multi-tenancy trait

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_capability_registry_tables.php`
  - `skill_definitions` — master skill catalogue: `company_id`, `name`, `category`, `description`, `requires_certification` (bool), `expiry_months` (nullable), `is_active`
  - `technician_skills` — user skill assignments: `user_id`, `skill_definition_id`, `level` (trainee|competent|proficient|expert), `acquired_at`, `expires_at`, `endorsed_by` (user_id), `is_verified` (bool)
  - `certifications` — certification records: `user_id`, `company_id`, `certification_name`, `issuing_body`, `certificate_number`, `issued_at`, `expires_at`, `document_path`, `status` (active|expired|revoked)
  - `skill_requirements` — skill rules per job type: `job_type_id`, `skill_definition_id`, `minimum_level`, `is_mandatory`
  - `availability_windows` — recurring availability: `user_id`, `day_of_week`, `start_time`, `end_time`, `is_active`
  - `availability_overrides` — one-off exceptions: `user_id`, `date`, `available` (bool), `reason`
- All tables use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/Team/SkillDefinition.php` — master skill with `BelongsToCompany`
- `app/Models/Team/TechnicianSkill.php` — pivot-style with level and expiry
- `app/Models/Team/Certification.php` — certification record with status scopes
- `app/Models/Team/SkillRequirement.php` — job type → skill mapping
- `app/Models/Team/AvailabilityWindow.php` — recurring schedule
- `app/Models/Team/AvailabilityOverride.php` — exception dates

### Services
- `app/Services/Team/CapabilityRegistryService.php`
  - `getSkillProfile(User $user): array` — full skill snapshot
  - `hasSkill(User $user, int $skillDefinitionId, string $minLevel = 'competent'): bool`
  - `hasCertification(User $user, string $certName): bool`
  - `getCertificationStatus(User $user, string $certName): string` — active|expired|missing
  - `getExpiringCertifications(int $companyId, int $withinDays = 30): Collection`
  - `isAvailable(User $user, Carbon $datetime): bool`
  - `getAvailableWindow(User $user, Carbon $date): ?array`
  - `matchJobRequirements(User $user, JobType $jobType): array` — returns matched[], missing[], expired[]
- `app/Services/Team/SkillComplianceService.php`
  - `getComplianceGaps(int $companyId): Collection`
  - `flagExpiringSkills(int $companyId): void`
  - `generateComplianceReport(int $companyId): array`

### Events
- `app/Events/Team/SkillAssigned.php`
- `app/Events/Team/CertificationExpired.php`
- `app/Events/Team/CertificationRevoked.php`
- `app/Events/Team/CapabilityGapDetected.php`

### Listeners
- `app/Listeners/Team/NotifyOnCertificationExpiry.php`
- `app/Listeners/Team/RecordCapabilityAuditTrail.php` — via `AuditTrail`

### Signals
- Emit via `SignalDispatcher` for: `capability.skill_assigned`, `capability.cert_expired`, `capability.gap_detected`
- Include technician_id, skill/cert details, company_id in signal context

### Controllers / Routes
- `app/Http/Controllers/Team/CapabilityController.php`
  - `profile(User $user)` — full capability profile
  - `skills(User $user)` — skill list with levels
  - `certifications(User $user)` — cert list with expiry status
  - `availability(User $user)` — availability windows
  - `gaps(Request $request)` — company-wide compliance gaps
- Register in `routes/core/team.php` under `capabilities` prefix

### Tests
- `tests/Unit/Services/Team/CapabilityRegistryServiceTest.php`
- `tests/Feature/Team/CapabilityControllerTest.php`

### Docs Report
- `docs/modules/MODULE_02_CapabilityRegistry_report.md` — skill graph schema, compliance model, dispatch integration map

### FSM Update
- Update `fsm_module_status.json` — set `capability_registry` to `installed`

---

## Architecture Notes

- Skill levels must be ordinal: trainee < competent < proficient < expert — `hasSkill()` must check ≥ minimum level
- Expired certifications must NOT satisfy `hasCertification()` checks — recency is a hard gate
- `matchJobRequirements()` result must feed directly into `DispatchConstraintService::evaluateSkillMatch()` (Module 01)
- Availability windows are recurring by day-of-week; overrides take precedence for specific dates
- Must respect `company_id` scoping — skill definitions are per-company, not global
- Follow existing `BelongsToCompany` and `OwnedByUser` trait patterns from `app/Models/Concerns/`
- All compliance gap queries must be optimised with proper indexes — this runs on every dispatch request

---

## References

- `app/Titan/Signals/SignalDispatcher.php`
- `app/Titan/Signals/AuditTrail.php`
- `app/Models/Concerns/BelongsToCompany.php`
- `app/Models/Concerns/OwnedByUser.php`
- `app/Models/Work/JobType.php`
- `app/Models/Work/ServiceJob.php`
- `app/Services/Work/DispatchConstraintService.php` (Module 01 output)
- `docs/nexuscore/` (workforce, skills, compliance docs)
- `docs/titancore/` (FSM and signal design)
- `CodeToUse/work/` (scan for skill/team entity files)
