# PropertyManagement — Tradie / Agent / Cleaner Focus

This module stores **Property (Jobsite)** profiles so field teams can work from a single, reliable snapshot.

## What it is
- Property details, access, hazards, keys, photos, checklists, and lightweight job logs.

## What it is NOT
- Not a replacement for core **Jobs / Work Orders / Quotes / Invoices**.
- Links to those modules via `linked_module` + `linked_id` fields on pm_property_jobs.

## Titan Zero
Uses `@includeIf('titanzero::partials.ask-titan', ...)` to request structured outputs without direct model/provider calls.
