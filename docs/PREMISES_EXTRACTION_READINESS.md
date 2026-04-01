# Premises Extraction Readiness — Stage D

## Scope

This document maps the overlap between CodeToUse source modules ahead of
FacilityManagement, AssetManagement, and SiteInspection extraction.

---

## Source Module Inventory

| Module | Path | Primary Entity | Key Tables (source) |
|--------|------|---------------|---------------------|
| ManagedPremises | CodeToUse/managed-premises/ManagedPremises | Property | pm_properties, pm_property_units, pm_property_contacts, pm_property_keys, pm_property_photos, pm_property_rooms, pm_property_hazards, pm_property_service_windows, pm_property_assets, pm_property_service_plans, pm_property_visits, pm_property_inspections, pm_property_documents |
| Units | CodeToUse/managed-premises/Units | Unit, Floor, Tower | units, floors, towers, type_units, users_units |
| FacilityManagement | CodeToUse/managed-premises/FacilityManagement | (TBD — scan required) | — |
| AssetManagement | CodeToUse/managed-premises/AssetManagement | (TBD — scan required) | — |
| SiteInspection | CodeToUse/managed-premises/SiteInspection | (TBD — scan required) | — |
| PropertyManagement | CodeToUse/managed-premises/PropertyManagement | (TBD — scan required) | — |
| Asset | CodeToUse/managed-premises/Asset | (TBD — scan required) | — |
| Houses | CodeToUse/managed-premises/Houses | (TBD — scan required) | — |

---

## Stage C Work Completed (this pass)

The following canonical host models have been created under `App\Models\Premises\`:

```
Premises    → table: premises
Building    → table: buildings
Floor       → table: premise_floors
Unit        → table: premise_units
Room        → table: rooms
```

### Deliberate naming choices

- `premise_floors` (not `floors`) avoids collision with legacy `floors` table from the Units module source.
- `premise_units` (not `units`) avoids collision with legacy `units` table from the Units module source.
- `rooms` was unused in the host; safe to own.

### ServiceJob and Equipment linkage

- `service_jobs.premises_id` added (FK → premises).
- `equipment.premises_id` added at creation time.
- `installed_equipment.premises_id` added at creation time.
- `equipment_movements.premises_id` added at creation time.

---

## Model Overlap Analysis

### ManagedPremises::Property ↔ Host Premises

| Aspect | ManagedPremises::Property | Host Premises |
|--------|--------------------------|---------------|
| Table | pm_properties | premises |
| Canonical source? | Source only | **Host canonical** |
| Hierarchy support | Single-level (units only) | Full hierarchy (Premises→Building→Floor→Unit→Room) |
| Site context memory | access_notes, hazards, lockbox, keys | ✓ Same fields extracted |
| Service window | preferred_window_start/end (datetime) | service_window_start/end (time) |
| Customer linkage | customer_id (nullable) | customer_id (nullable) ✓ |
| Deferred fields | service_plans, inspections, meter_readings | Next pass |

### Units::Floor / Unit / Tower ↔ Host Premise Floor / Unit

| Aspect | Units::Floor/Tower | Host Floor/Unit |
|--------|-------------------|-----------------|
| Table | floors / towers / units | premise_floors / premise_units |
| Hierarchy | tower → floor → unit | Premises → Building → Floor → Unit → Room |
| Tower maps to | — | Building (renamed; richer) |
| Floor maps to | — | Floor ✓ |
| Unit maps to | — | Unit ✓ |
| company_id | yes | yes ✓ |
| Merge action | Bridge if needed | **Host canonical** |

---

## Models That Duplicate

| Duplicates | Canonical | Action |
|-----------|-----------|--------|
| ManagedPremises::Property | Host::Premises | Bridge / migrate data |
| Units::Floor | Host::Premises::Floor | Bridge — different table name |
| Units::Tower | Host::Premises::Building | Bridge — semantic rename |
| Units::Unit | Host::Premises::Unit | Bridge — different table name |

---

## Models That Extend Each Other

| Source Model | Extends | Notes |
|-------------|---------|-------|
| pm_property_assets | Equipment domain | AssetManagement pass |
| pm_property_inspections | SiteInspection domain | Inspection pass |
| pm_property_service_plans | ServiceAgreement | Agreement integration pass |
| pm_property_visits | ServiceJob | Job integration pass |
| pm_property_keys | Premises context memory | Already extracted as keys_location / lockbox_code |

---

## Extraction Readiness Status

| Module | Status | Dependency |
|--------|--------|-----------|
| Premises hierarchy | ✅ Done (this pass) | None |
| FacilityManagement | 🔜 Ready for next pass | Premises hierarchy ✅ |
| AssetManagement | 🔜 Ready for next pass | Equipment domain ✅ |
| SiteInspection | 🔜 Ready for next pass | Premises hierarchy ✅ |
| ManagedPremises full merge | 🔜 Ready for next pass | Premises + Equipment ✅ |
| Units bridge | 🔜 Ready for next pass | premise_floors + premise_units ✅ |

---

## Next Pass Extraction Plan

1. **FacilityManagement** — scan source, extract maintenance schedules and facility-level work orders; link to Premises.
2. **AssetManagement** — scan source, bridge pm_property_assets → Equipment; avoid duplicate asset tables.
3. **SiteInspection** — scan source, extract inspection checklists and results; link to Premises and ServiceJob.
4. **ManagedPremises full merge** — migrate pm_properties → premises, migrate pm_property_units → premise_units.
5. **Units bridge** — add migration to map floors/towers/units records into premise hierarchy if data exists.

---

*Generated: 2026-04-01 — Stage D readiness document*
